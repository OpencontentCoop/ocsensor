<?php

namespace Opencontent\Stanzadelcittadino\Client\Request;

use Opencontent\Stanzadelcittadino\Client\AbstractRequestHandler;
use Opencontent\Stanzadelcittadino\Client\Credential;
use Opencontent\Stanzadelcittadino\Client\Exceptions\FailPatchApplicationBinaryWithExternalId;
use Psr\Http\Client\ClientExceptionInterface;

class PatchApplicationMessageWithExternalId extends AbstractRequestHandler
{
    /**
     * @var string
     */
    private $applicationId;

    /**
     * @var string
     */
    private $messageId;

    /**
     * @var string
     */
    private $messageExternalId;

    public function __construct(
        string $applicationId,
        string $messageId,
        string $messageExternalId
    ) {
        $this->applicationId = $applicationId;
        $this->messageId = $messageId;
        $this->messageExternalId = $messageExternalId;
    }

    public function getRequestMethod(): string
    {
        return 'PATCH';
    }

    public function getRequestPath(): string
    {
        return '/applications/' . $this->applicationId . '/messages/' . $this->messageId;
    }

    public function getMinimumCredential(): ?string
    {
        return Credential::API_USER;
    }

    public function getRequestOptions(): array
    {
        return [
            'json' => [
                'external_id' => $this->messageExternalId,
            ],
        ];
    }

    public function handleError(\Throwable $e)
    {
        if ($e instanceof ClientExceptionInterface) {
            throw new FailPatchApplicationBinaryWithExternalId();
        }
    }

    public function handleResponse(string $response): ?array
    {
        return [
            'id' => $this->messageId,
            'external_id' => $this->messageExternalId,
        ];
    }

}