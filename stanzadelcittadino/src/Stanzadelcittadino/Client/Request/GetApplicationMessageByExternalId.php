<?php

namespace Opencontent\Stanzadelcittadino\Client\Request;

use Opencontent\Stanzadelcittadino\Client\Exceptions\MessageNotFound;
use Psr\Http\Client\ClientExceptionInterface;

class GetApplicationMessageByExternalId extends GetApplicationByUuid
{
    private $messageExternalId;

    public function __construct(string $messageExternalId, string $uuid, int $version = 1)
    {
        $this->messageExternalId = $messageExternalId;
        parent::__construct($uuid, $version);
    }

    public function getRequestPath(): string
    {
        return parent::getRequestPath() . '/messages/byexternal-id/' . $this->messageExternalId;
    }

    public function handleError(\Throwable $e)
    {
        if ($e instanceof ClientExceptionInterface) {
            throw new MessageNotFound();
        }
    }

}