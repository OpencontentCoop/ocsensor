<?php

namespace Opencontent\Stanzadelcittadino\Client\Request;

use Opencontent\Stanzadelcittadino\Client\AbstractRequestHandler;
use Opencontent\Stanzadelcittadino\Client\Credential;
use Opencontent\Stanzadelcittadino\Client\Exceptions\FailApplicationCreate;
use Psr\Http\Client\ClientExceptionInterface;

class CreateApplication extends AbstractRequestHandler
{
    private $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }

    public function getRequestPath(): string
    {
        return '/applications';
    }

    public function getMinimumCredential(): ?string
    {
        return Credential::USER;
    }

    public function getRequestOptions(): array
    {
        return ['json' => $this->payload];
    }

    public function handleError(\Throwable $e)
    {
        if ($e instanceof ClientExceptionInterface){
            throw new FailApplicationCreate();
        }
    }
}