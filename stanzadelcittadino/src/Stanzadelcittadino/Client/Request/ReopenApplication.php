<?php

namespace Opencontent\Stanzadelcittadino\Client\Request;

use Opencontent\Stanzadelcittadino\Client\AbstractRequestHandler;
use Opencontent\Stanzadelcittadino\Client\Exceptions\FailReopenApplication;
use Psr\Http\Client\ClientExceptionInterface;

class ReopenApplication extends AbstractRequestHandler
{
    private $applicationUuid;

    public function __construct(string $applicationUuid)
    {
        $this->applicationUuid = $applicationUuid;
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }

    public function getRequestPath(): string
    {
        return '/applications/' . $this->applicationUuid . '/transition/change-status';
    }

    public function getRequestOptions(): array
    {
        return ['json' => ['status' => 2000,]];
    }

    public function handleError(\Throwable $e)
    {
        if ($e instanceof ClientExceptionInterface) {
            throw new FailReopenApplication();
        }
    }

}