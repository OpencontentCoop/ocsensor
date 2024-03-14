<?php

namespace Opencontent\Stanzadelcittadino\Client;

class Credential
{
    const ANONYMOUS = 'anonymous';

    const USER = 'user';

    const OPERATOR = 'operator';

    const API_USER = 'api_user';

    const ADMIN = 'admin';

    public $identifier;

    public $user;

    public $password;

    /** @var ?string */
    private $accessToken = null;

    public function __construct(string $identifier, ?string $user, ?string $password)
    {
        if (!in_array($identifier, CredentialSet::AVAILABLE_IDENTIFIERS)) {
            throw new \InvalidArgumentException("Credential identifier $identifier not allowed");
        }
        $this->identifier = $identifier;
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * @param string|null $accessToken
     */
    public function setAccessToken(?string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function canHaveAccessToken(): bool
    {
        return !empty($this->user) && !empty($this->password);
    }

    public function haveAccessToken(): bool
    {
        return !empty($this->accessToken);
    }

    public function getProperties(): ?array
    {
        if ($this->haveAccessToken()){
            list($header, $payload, $signature) = explode (".", $this->getAccessToken());
            return json_decode(base64_decode($payload), true);
        }

        return null;
    }
}