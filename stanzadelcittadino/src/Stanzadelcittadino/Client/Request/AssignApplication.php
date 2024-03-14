<?php

namespace Opencontent\Stanzadelcittadino\Client\Request;

use Opencontent\Stanzadelcittadino\Client\AbstractRequestHandler;
use Opencontent\Stanzadelcittadino\Client\Credential;
use Opencontent\Stanzadelcittadino\Client\Exceptions\FailAssignApplication;
use Psr\Http\Client\ClientExceptionInterface;
use DateTimeInterface;

class AssignApplication extends AbstractRequestHandler
{
    /**
     * @var string
     */
    private $applicationUuid;

    /**
     * @var string|null
     */
    private $userGroupUuid;

    /**
     * @var string|null
     */
    private $userUuid;

    /**
     * @var string|null
     */
    private $message;

    /**
     * @var DateTimeInterface|null
     */
    private $assignedAt;

    public function __construct(
        string $applicationUuid,
        ?string $userGroupUuid,
        ?string $userUuid = null,
        ?string $message = null,
        ?DateTimeInterface $assignedAt = null
    ) {
        $this->applicationUuid = $applicationUuid;
        $this->userGroupUuid = $userGroupUuid;
        $this->userUuid = $userUuid;
        $this->message = $message;
        $this->assignedAt = $assignedAt;
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }

    public function getRequestPath(): string
    {
        return '/applications/' . $this->applicationUuid . '/transition/assign';
    }

    public function getMinimumCredential(): ?string
    {
        return Credential::API_USER;
    }

    public function getRequestOptions(): array
    {
        $data = [
            'user_group_id' => $this->userGroupUuid,
            'user_id' => $this->userUuid ?? null,
        ];
        if (!empty($this->message) && $this->assignedAt instanceof DateTimeInterface) {
            $data['message'] = $this->message;
            $data['assigned_at'] = $this->assignedAt->format('c');
        }
        return ['json' => $data];
    }

    public function handleError(\Throwable $e)
    {
        if ($e instanceof ClientExceptionInterface) {
            throw new FailAssignApplication();
        }
    }


}