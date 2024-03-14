<?php

namespace Opencontent\Stanzadelcittadino\Client\Request;

use Opencontent\Stanzadelcittadino\Client\AbstractRequestHandler;
use Opencontent\Stanzadelcittadino\Client\Credential;
use Opencontent\Stanzadelcittadino\Client\Exceptions\FailPutBinaryToApplication;
use Psr\Http\Client\ClientExceptionInterface;

class PutBinaryToApplication extends AbstractRequestHandler
{

    /**
     * @var array
     */
    private $uploadFile;

    /**
     * @var array
     */
    private $application;

    /**
     * @var string
     */
    private $dataKey;

    /**
     * @var string
     */
    private $externalId;

    public function __construct(
        array $uploadFile,
        array $application,
        string $dataKey,
        ?string $externalId
    ) {
        $this->uploadFile = $uploadFile;
        $this->application = $application;
        $this->dataKey = $dataKey;
        $this->externalId = $externalId;
    }

    public function getRequestMethod(): string
    {
        return 'PUT';
    }

    public function getRequestPath(): string
    {
        return '/applications/' . $this->application['id'];
    }

    public function getMinimumCredential(): ?string
    {
        return Credential::OPERATOR;
    }

    public function getRequestOptions(): array
    {
        foreach (['images', 'docs'] as $key) {
            $dataByKey = $application['data'][$key] ?? [];
            foreach ($dataByKey as $index => $item) {
                $this->application['data'][$key][$index]['data']['id'] = $item['id'];
            }
        }
        $currentDataKey = $application['data'][$this->dataKey] ?? [];
        $requestData = $this->application;
        if (isset($requestData['payment_data']) && empty($requestData['payment_data'])) {
            unset($requestData['payment_data']);
        }
        if (empty($requestData['external_id']) && $this->externalId) {
            $requestData['external_id'] = $this->externalId;
        }
        $requestData['data'] = array_merge($this->application['data'], [
            "applicant" => [
                "data" => [
                    "email_address" => $this->application['data']['applicant.data.email_address'] ?? '',
                    "phone_number" => $this->application['data']['applicant.data.phone_number'] ?? '',
                    "completename" => [
                        "data" => [
                            "name" => $this->application['data']['applicant.data.completename.data.name'] ?? '',
                            "surname" => $this->application['data']['applicant.data.completename.data.surname'] ?? '',
                        ],
                    ],
                    "fiscal_code" => [
                        "data" => [
                            "fiscal_code" => $this->application['data']['applicant.data.fiscal_code.data.fiscal_code'] ?? '',
                        ],
                    ],
                    "person_identifier" => $this->application['data']['applicant.data.person_identifier'] ?? '',
                ],
            ],
            $this->dataKey => array_merge($currentDataKey, [
                [
                    'id' => $this->uploadFile['data']['id'],
                    'url' => $this->uploadFile['data']['uri'],
                    'data' => $this->uploadFile['data'],
                    'name' => $this->uploadFile['name'],
                    'original_filename' => $this->uploadFile['name'],
                    'size' => $this->uploadFile['size'],
                    'protocol_required' => false,
                    'mime_type' => $this->uploadFile['type'],
                ],
            ]),
        ]);

        return ['json' => $requestData,];
    }

    public function handleError(\Throwable $e)
    {
        if ($e instanceof ClientExceptionInterface) {
            throw new FailPutBinaryToApplication();
        }
    }

}