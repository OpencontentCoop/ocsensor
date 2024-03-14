<?php

namespace Opencontent\Stanzadelcittadino\Client;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Opencontent\Stanzadelcittadino\Client\Exceptions\AvoidAccessToken;
use Opencontent\Stanzadelcittadino\Client\Exceptions\FailBinaryCreate;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Throwable;

class HttpClient implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected $client;

    protected $locale;

    protected $baseUri;

    protected $apiUri;

    protected $credentialSet;

    /**
     * @var Credential
     */
    protected $currentCredential;

    public function __construct(
        ?string $baseUri = null,
        ?string $locale = null
    ) {
        $baseUri = $baseUri ?? getenv('SDC_BASENAME');
        if ($baseUri) {
            $this->setBaseUri($baseUri);
        }
        $this->locale = $locale ?? 'it';
        $this->client = new GuzzleClient();
        $this->logger = new NullLogger();
        $this->credentialSet = new CredentialSet();
        $this->currentCredential = $this->credentialSet->get(Credential::ANONYMOUS);
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): HttpClient
    {
        $this->locale = $locale;
        return $this;
    }

    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    public function setBaseUri(string $baseUri): HttpClient
    {
        $baseUri = rtrim($baseUri, '/');
        $this->baseUri = $baseUri;
        $this->setApiUri($baseUri . '/api');
        return $this;
    }

    public function getApiUri(): string
    {
        return $this->apiUri;
    }

    public function setApiUri(string $apiUri): HttpClient
    {
        $apiUri = rtrim($apiUri, '/');
        $this->apiUri = $apiUri;
        return $this;
    }

    public function addCredential(string $identifier, string $user, string $password): HttpClient
    {
        $this->credentialSet->add(new Credential($identifier, $user, $password));
        $this->currentCredential = $this->credentialSet->get($identifier);
        return $this;
    }

    public function getCredential(string $identifier, bool $withAccessToken = false): ?Credential
    {
        $credential = $this->credentialSet->get($identifier);
        if ($credential
            && $withAccessToken
            && $credential->canHaveAccessToken()
            && !$credential->haveAccessToken()) {
            $credential->setAccessToken($this->fetchAccessToken($credential));
        }

        return $credential;
    }

    public function getCredentials(): CredentialSet
    {
        return $this->credentialSet;
    }

    public function getCurrentCredential(): ?Credential
    {
        return $this->currentCredential;
    }

    /**
     * @throws AvoidAccessToken
     * @throws GuzzleException
     */
    public function as($identifier): HttpClient
    {
        $this->currentCredential = $this->getCredential($identifier, true);
        return $this;
    }

    /**
     * @throws Throwable
     * @throws GuzzleException
     */
    public function __invoke(RequestHandlerInterface $handler, ?string $sendAsRole = null)
    {
        if ($sendAsRole && !$this->credentialSet->has($sendAsRole)) {
            throw new \InvalidArgumentException(
                'Cannot send request %s as role %s', $handler, $sendAsRole
            );
        }

        try {
            $this->logger->debug(sprintf('Handle request %s', $handler));
            if ($handler instanceof LoggerAwareInterface) {
                $handler->setLogger($this->logger);
            }
            $headers = [
                'Content-Type' => 'application/json',
            ];
            if ($sendAsRole) {
                $this->as($sendAsRole);
            } elseif ($credentialIdentifier = $handler->getMinimumCredential()) {
                $minimumCredentialReached = $this->getMinimumCredential($credentialIdentifier);
                if ($minimumCredentialReached) {
                    $this->logger->debug(sprintf('Authenticate as %s', $minimumCredentialReached));
                    $this->as($minimumCredentialReached);
                }
            }
            if ($this->currentCredential->haveAccessToken()) {
                $headers['Authorization'] = 'Bearer ' . $this->currentCredential->getAccessToken();
            }
            $response = (string)$this->request(
                strtoupper($handler->getRequestMethod()),
                $this->apiUri . $handler->getRequestPath(),
                array_merge_recursive(['headers' => $headers], $handler->getRequestOptions())
            )->getBody();

            return $handler->handleResponse($response) ?? json_decode($response, true);
        } catch (Throwable $e) {
            if ($error = $handler->handleError($e)) {
                return $error;
            }
            throw $e;
        }
    }

    private function getMinimumCredential($credentialIdentifier): ?string
    {
        $minimumReached = false;
        foreach (CredentialSet::AVAILABLE_IDENTIFIERS as $identifier) {
            if (!$minimumReached && $identifier == $credentialIdentifier) {
                $minimumReached = true;
            }
            if ($minimumReached) {
                if ($this->credentialSet->has($identifier)) {
                    return $identifier;
                }
            }
        }

        return null;
    }

    /**
     * @throws GuzzleException
     */
    public function request(string $method, string $uri = '', array $options = []): ResponseInterface
    {
        return $this->client->request(
            $method,
            $uri,
            $options
        );
    }

    public function uploadBinary(string $filePath, string $fileName, string $mimeType): array
    {
        try {
            if (!file_exists($filePath)) {
                throw new \RuntimeException('File ' . $filePath . ' not found');
            }
            $this->logger->debug(" - Get upload pre-signed uri for " . $fileName);

            $fileContents = file_get_contents($filePath);
            if (strlen($fileContents) === 0) {
                $this->logger->error("Error: invalid file contents for file " . $filePath);
                $fileContents = '[File not found]';
            }
            $size = strlen($fileContents);

            $headers = [
                'Content-Type' => 'application/json',
            ];
            if ($this->currentCredential->haveAccessToken()) {
                $headers['Authorization'] = 'Bearer ' . $this->currentCredential->getAccessToken();
            }

            $response = (string)$this->request(
                'POST',
                $this->getBaseUri() . '/' . $this->locale . '/upload',
                [
                    'headers' => $headers,
                    'json' => [
                        'name' => $fileName,
                        'original_filename' => $fileName,
                        'size' => $size,
                        'protocol_required' => false,
                        'mime_type' => $mimeType,
                    ],
                ]
            )->getBody();
            $fileInfo = json_decode($response, true);

            $this->logger->debug(" - Put file to " . substr($fileInfo['uri'], 0, 100) . '...');
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $fileInfo['uri'],
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_CUSTOMREQUEST => "PUT",
                CURLOPT_ENCODING => "",
                CURLOPT_POST => 1,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 300,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_INFILESIZE => $size,
                CURLOPT_HTTPHEADER => [
                    "Accept: */*",
                    "Accept-Encoding: gzip, deflate",
                    "Cache-Control: no-cache",
                    "Connection: keep-alive",
                    "Content-Length: " . $size,
                    "Content-Type: multipart/form-data",
                ],
            ]);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $fileContents);
            curl_exec($curl);

            $this->logger->debug(" - Update upload " . $fileInfo['id']);
            $fileHash = hash('sha256', $fileContents);

            $this->client->request(
                'PUT',
                $this->getBaseUri() . '/' . $this->locale . '/upload/' . $fileInfo['id'],
                [
                    'headers' => $headers,
                    'json' => [
                        'file_hash' => $fileHash,
                        'check_signature' => false,
                    ],
                ]
            )->getBody();

            return [
                "name" => $fileName,
                "url" => null,
                "size" => $size,
                "type" => $mimeType,
                "data" => $fileInfo,
                "originalName" => $fileName,
                "hash" => $fileHash,
                "preview" => null,
            ];
        } catch (ClientExceptionInterface $e) {
            throw new FailBinaryCreate();
        }
    }

    public function downloadBinary($url): ?string
    {
        $credentialIdentifier = $this->getMinimumCredential(Credential::API_USER);
        if ($credentialIdentifier) {
            $credential = $this->getCredential($credentialIdentifier, true);
            $token = $credential->getAccessToken();
            $context = stream_context_create([
                'http' => [
                    'header' => 'Authorization: Bearer ' . $token,
                ],
            ]);
            $data = file_get_contents($url, false, $context);
            if (!$data) {
                return null;
            }
            return $data;
        }
        $this->logger->warning('Not enough privileges to download binaries');
        return null;
    }

    /**
     * @throws GuzzleException|AvoidAccessToken
     */
    private function fetchAccessToken(Credential $credential): ?string
    {
        if (!$credential->canHaveAccessToken()) {
            throw new AvoidAccessToken('Can not retrieve access token for credential ' . $credential->identifier);
        }
        $this->logger->debug(sprintf('Request access token for %s', $credential->identifier));
        return json_decode(
            (string)$this->client->request(
                'POST',
                $this->apiUri . '/auth',
                [
                    'json' => [
                        'username' => $credential->user,
                        'password' => $credential->password,
                    ],
                ]
            )->getBody(),
            true
        )['token'];
    }
}