<?php

namespace Opencontent\Stanzadelcittadino\Client\Request;

use GuzzleHttp\Exception\ClientException;
use Opencontent\Stanzadelcittadino\Client\AbstractRequestHandler;
use Opencontent\Stanzadelcittadino\Client\Credential;
use Opencontent\Stanzadelcittadino\Client\Exceptions\ApplicationNotFound;

class GetApplicationByUuid extends AbstractRequestHandler
{
    protected $uuid;

    protected $version;

    public function __construct(string $uuid, int $version = 1)
    {
        $this->uuid = $uuid;
        $this->version = $version;
    }

    public function getRequestPath(): string
    {
        return '/applications/' . $this->uuid;
    }

    public function getMinimumCredential(): ?string
    {
        return Credential::USER;
    }

    public function handleError(\Throwable $e)
    {
        if ($e instanceof ClientException && $e->getResponse()->getStatusCode() === 404) {
            throw new ApplicationNotFound();
        }
    }

    public function getRequestOptions(): array
    {
        return ['version' => $this->version];
    }
}