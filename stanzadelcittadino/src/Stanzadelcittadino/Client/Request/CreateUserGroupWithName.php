<?php

namespace Opencontent\Stanzadelcittadino\Client\Request;

use Opencontent\Stanzadelcittadino\Client\AbstractRequestHandler;
use Opencontent\Stanzadelcittadino\Client\Credential;
use Opencontent\Stanzadelcittadino\Client\Exceptions\FailCreateUserGroup;
use Psr\Http\Client\ClientExceptionInterface;

class CreateUserGroupWithName extends AbstractRequestHandler
{
    /**
     * @var string
     */
    private $userGroupName;

    public function __construct(string $userGroupName)
    {
        $this->userGroupName = trim($userGroupName);
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }

    public function getRequestPath(): string
    {
        return '/user-groups';
    }

    public function getMinimumCredential(): ?string
    {
        return Credential::ADMIN;
    }

    public function getRequestOptions(): array
    {
        return [
            'json' => [
                'name' => $this->userGroupName,
            ],
        ];
    }

    public function handleError(\Throwable $e)
    {
        if ($e instanceof ClientExceptionInterface) {
            throw new FailCreateUserGroup();
        }
    }

}