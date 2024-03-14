<?php

namespace Opencontent\Stanzadelcittadino\Client\Request;

use GuzzleHttp\Exception\ClientException;
use Opencontent\Stanzadelcittadino\Client\AbstractRequestHandler;
use Opencontent\Stanzadelcittadino\Client\Credential;
use Opencontent\Stanzadelcittadino\Client\Exceptions\UserByIdNotFound;

class GetUserByUuid extends AbstractRequestHandler
{
    protected $uuid;

    public function __construct(string $uuid)
    {
        $this->uuid = $uuid;
    }

    public function getRequestPath(): string
    {
        return '/users/' . $this->uuid;
    }

    public function getMinimumCredential(): ?string
    {
        return Credential::USER;
    }

    public function handleError(\Throwable $e)
    {
        if ($e instanceof ClientException && $e->getResponse()->getStatusCode() === 404) {
            throw new UserByIdNotFound();
        }
    }
}