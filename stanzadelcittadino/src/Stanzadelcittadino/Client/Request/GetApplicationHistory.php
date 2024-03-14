<?php

namespace Opencontent\Stanzadelcittadino\Client\Request;

class GetApplicationHistory extends GetApplicationByUuid
{
    public function getRequestPath(): string
    {
        return parent::getRequestPath() . '/history';
    }
}