<?php

namespace Opencontent\Stanzadelcittadino\Client\Request;

use Opencontent\Stanzadelcittadino\Client\AbstractRequestHandler;
use Opencontent\Stanzadelcittadino\Client\Credential;
use Opencontent\Stanzadelcittadino\Client\Exceptions\FailAcceptApplication;
use Psr\Http\Client\ClientExceptionInterface;

class AcceptApplication extends AbstractRequestHandler
{
    /**
     * @var array
     */
    private $applicationUuid;

    /**
     * @var string|null
     */
    private $message;

    public function __construct(string $applicationUuid, ?string $message = null)
    {
        $this->applicationUuid = $applicationUuid;
        $this->message = $message;
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }

    public function getRequestPath(): string
    {
        return '/applications/' . $this->applicationUuid . '/transition/accept';
    }

    public function getMinimumCredential(): ?string
    {
        return Credential::OPERATOR;
    }

    public function getRequestOptions(): array
    {
        $data = $this->message ? ['message' => $this->message,] : [];
        return ['json' => $data];
    }

    public function handleError(\Throwable $e)
    {
        if ($e instanceof ClientExceptionInterface) {
            throw new FailAcceptApplication();
        }
    }

}