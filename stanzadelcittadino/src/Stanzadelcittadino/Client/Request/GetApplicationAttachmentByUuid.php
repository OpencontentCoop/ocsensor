<?php

namespace Opencontent\Stanzadelcittadino\Client\Request;

use Opencontent\Stanzadelcittadino\Client\AbstractRequestHandler;

class GetApplicationAttachmentByUuid extends GetApplicationByUuid
{
    private $attachmentId;

    public function __construct(string $applicationUuid, string $attachmentId, int $version = 1)
    {
        $this->attachmentId = $attachmentId;
        parent::__construct($applicationUuid, $version);
    }

    public function getRequestPath(): string
    {
        return parent::getRequestPath() . '/attachments/' . $this->attachmentId;
    }
}