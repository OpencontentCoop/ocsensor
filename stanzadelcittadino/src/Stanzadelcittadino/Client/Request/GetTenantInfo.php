<?php

namespace Opencontent\Stanzadelcittadino\Client\Request;

use Opencontent\Stanzadelcittadino\Client\AbstractRequestHandler;

class GetTenantInfo extends AbstractRequestHandler
{
    protected $method = 'GET';

    protected $path = '/tenants/info';
}