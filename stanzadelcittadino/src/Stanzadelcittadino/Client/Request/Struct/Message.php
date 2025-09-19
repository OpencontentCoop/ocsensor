<?php

namespace Opencontent\Stanzadelcittadino\Client\Request\Struct;

class Message extends AbstractStruct
{
    public $message;

    public $visibility;

    public $sent_at;

    public $external_id;

    public $attachments;
}