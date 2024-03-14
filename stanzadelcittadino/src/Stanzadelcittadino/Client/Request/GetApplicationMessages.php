<?php

namespace Opencontent\Stanzadelcittadino\Client\Request;

use Opencontent\Stanzadelcittadino\Client\Exceptions\FailApplicationMessages;
use Psr\Http\Client\ClientExceptionInterface;

class GetApplicationMessages extends GetApplicationByUuid
{
    public function handleError(\Throwable $e)
    {
        if ($e instanceof ClientExceptionInterface) {
            throw new FailApplicationMessages();
        }
    }

    public function getRequestPath(): string
    {
        return parent::getRequestPath() . '/messages';
    }
}