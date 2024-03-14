<?php

namespace Opencontent\Stanzadelcittadino\Client\Request;

use Opencontent\Stanzadelcittadino\Client\AbstractRequestHandler;
use Opencontent\Stanzadelcittadino\Client\Credential;
use Opencontent\Stanzadelcittadino\Client\Exceptions\UserByFiscalCodeNotFound;

class GetUserByFiscalCode extends AbstractRequestHandler
{
    protected $fiscalCode;

    public function __construct(string $fiscalCode)
    {
        $this->fiscalCode = $fiscalCode;
    }

    public function getRequestPath(): string
    {
        return '/users';
    }

    public function getMinimumCredential(): ?string
    {
        return Credential::ADMIN;
    }

    public function handleResponse(string $response): ?array
    {
        $response = json_decode($response, true);
        if (count($response) > 0) {
            return $response[0];
        }

        throw new UserByFiscalCodeNotFound();
    }

    public function getRequestOptions(): array
    {
        return ['query' => ['cf' => $this->fiscalCode]];
    }


}