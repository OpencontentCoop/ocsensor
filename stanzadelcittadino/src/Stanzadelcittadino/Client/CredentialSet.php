<?php

namespace Opencontent\Stanzadelcittadino\Client;

class CredentialSet
{
    const AVAILABLE_IDENTIFIERS = [
        Credential::ANONYMOUS,
        Credential::USER,
        Credential::OPERATOR,
        Credential::API_USER,
        Credential::ADMIN,
    ];

    private $data;

    public function __construct($array = [])
    {
        $array[Credential::ANONYMOUS] = $this->getAnonymousCredential();
        $this->data = $array;
    }

    public function has($key): bool
    {
        return isset($this->data[$key]);
    }

    public function get($key)
    {
        if ($key === Credential::ANONYMOUS) {
            return $this->getAnonymousCredential();
        }
        return $this->data[$key] ?? null;
    }

    public function add(Credential $credential)
    {
        if ($credential->identifier === Credential::ANONYMOUS) {
            $credential = $this->getAnonymousCredential();
        }

        $this->data[$credential->identifier] = $credential;
    }

    private function getAnonymousCredential(): Credential
    {
        return new Credential(Credential::ANONYMOUS, null, null);
    }
}