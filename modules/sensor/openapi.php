<?php

$siteUrl = '/';
eZURI::transformURI($siteUrl,true, 'full');

$endpointUrl = '/api/sensor';
eZURI::transformURI($endpointUrl, true, 'full');

$openApiTools = new \Opencontent\Sensor\OpenApi(
    OpenPaSensorRepository::instance(),
    $siteUrl,
    $endpointUrl
);

if ('application/json' == strtolower($_SERVER['HTTP_ACCEPT']) || $Params['FunctionName'] == 'openapi.json') {
    header('Content-Type: application/json');
    echo json_encode($openApiTools->loadSchema());
    eZExecution::cleanExit();
}

$tpl = eZTemplate::factory();
echo $tpl->fetch('design:openapi.tpl');

eZDisplayDebug();
eZExecution::cleanExit();