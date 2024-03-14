<?php

namespace Opencontent\Stanzadelcittadino\Client\Request;

use GuzzleHttp\Exception\ClientException;
use Opencontent\Stanzadelcittadino\Client\AbstractRequestHandler;
use Opencontent\Stanzadelcittadino\Client\Credential;
use Opencontent\Stanzadelcittadino\Client\Exceptions\ApplicationByExternalIdNotFound;

class GetApplicationByExternalId extends AbstractRequestHandler
{
    private $externalId;

    private $version;

    public function __construct(string $externalId, int $version = 1)
    {
        $this->externalId = $externalId;
        $this->version = $version;
    }

    public function getRequestPath(): string
    {
        return '/applications/byexternal-id/' . $this->externalId;
    }

    public function getMinimumCredential(): ?string
    {
        return Credential::USER;
    }

    public function handleError(\Throwable $e)
    {
        if ($e instanceof ClientException && $e->getResponse()->getStatusCode() === 404) {
            throw new ApplicationByExternalIdNotFound();
        }
    }

    public function getRequestOptions(): array
    {
        return ['version' => $this->version];
    }
}