<?php

namespace Opencontent\Stanzadelcittadino\Client\Request;

use Opencontent\Stanzadelcittadino\Client\AbstractRequestHandler;
use Opencontent\Stanzadelcittadino\Client\Credential;
use Opencontent\Stanzadelcittadino\Client\Exceptions\FailCreateMessage;
use Opencontent\Stanzadelcittadino\Client\Request\Struct\Message;
use Psr\Http\Client\ClientExceptionInterface;

class CreateApplicationMessage extends AbstractRequestHandler
{
    /**
     * @var string
     */
    private $applicationUuid;

    /**
     * @var Message
     */
    private $message;

    public function __construct(string $applicationUuid, Message $message)
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
        return '/applications/' . $this->applicationUuid . '/messages';
    }

    public function getMinimumCredential(): ?string
    {
        return Credential::USER;
    }

    public function getRequestOptions(): array
    {
        return ['json' => $this->message->toArray()];
    }

    public function handleError(\Throwable $e)
    {
        if ($e instanceof ClientExceptionInterface) {
            throw new FailCreateMessage();
        }
    }
}