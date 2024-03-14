<?php

namespace Opencontent\Stanzadelcittadino\Client\Request;

use Opencontent\Stanzadelcittadino\Client\AbstractRequestHandler;
use Opencontent\Stanzadelcittadino\Client\Credential;
use Opencontent\Stanzadelcittadino\Client\Exceptions\FailUserCreate;
use Opencontent\Stanzadelcittadino\Client\Request\Struct\User;
use Psr\Http\Client\ClientExceptionInterface;

class CreateUser extends AbstractRequestHandler
{
    private $payload;

    public function __construct(User $payload)
    {
        $this->payload = $payload;
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }

    public function getRequestPath(): string
    {
        return '/users';
    }

    public function getMinimumCredential(): ?string
    {
        return Credential::ADMIN;
    }

    public function getRequestOptions(): array
    {
        return ['json' => $this->payload->toArray()];
    }

    public function handleError(\Throwable $e)
    {
        if ($e instanceof ClientExceptionInterface) {
            throw new FailUserCreate();
        }
    }

}