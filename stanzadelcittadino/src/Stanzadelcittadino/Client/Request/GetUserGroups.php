<?php

namespace Opencontent\Stanzadelcittadino\Client\Request;

use Opencontent\Stanzadelcittadino\Client\AbstractRequestHandler;
use Opencontent\Stanzadelcittadino\Client\Credential;
use Opencontent\Stanzadelcittadino\Client\Exceptions\FailGetUserGroups;
use Psr\Http\Client\ClientExceptionInterface;

class GetUserGroups extends AbstractRequestHandler
{
    public function getMinimumCredential(): ?string
    {
        return Credential::ADMIN;
    }

    public function getRequestPath(): string
    {
        return '/user-groups';
    }

    public function handleError(\Throwable $e)
    {
        if ($e instanceof ClientExceptionInterface){
            throw new FailGetUserGroups();
        }
    }

}