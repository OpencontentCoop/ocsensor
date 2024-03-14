<?php

namespace Opencontent\Stanzadelcittadino\Client\Request;

use Opencontent\Stanzadelcittadino\Client\AbstractRequestHandler;
use Opencontent\Stanzadelcittadino\Client\Credential;
use Opencontent\Stanzadelcittadino\Client\Exceptions\FailPatchApplicationBinaryWithExternalId;
use Psr\Http\Client\ClientExceptionInterface;

class PatchApplicationAttachmentWithExternalId extends AbstractRequestHandler
{
    /**
     * @var string
     */
    private $applicationId;

    /**
     * @var string
     */
    private $attachmentId;

    /**
     * @var string
     */
    private $attachmentExternalId;

    public function __construct(
        string $applicationId,
        string $attachmentId,
        string $attachmentExternalId
    ) {
        $this->applicationId = $applicationId;
        $this->attachmentId = $attachmentId;
        $this->attachmentExternalId = $attachmentExternalId;
    }

    public function getRequestMethod(): string
    {
        return 'PATCH';
    }

    public function getRequestPath(): string
    {
        return '/applications/' . $this->applicationId . '/attachments/' . $this->attachmentId;
    }

    public function getMinimumCredential(): ?string
    {
        return Credential::API_USER;
    }

    public function getRequestOptions(): array
    {
        return [
            'json' => [
                'external_id' => $this->attachmentExternalId,
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
            'id' => $this->attachmentId,
            'external_id' => $this->attachmentExternalId,
        ];
    }

}