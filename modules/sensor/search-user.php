<?php

use Opencontent\Opendata\Api\ContentSearch;
use Opencontent\Opendata\Api\EnvironmentLoader;

$fiscalCode = $Params['FiscalCode'];

$currentEnvironment = EnvironmentLoader::loadPreset('content');
$parser = new ezpRestHttpRequestParser();
$request = $parser->createRequest();
$currentEnvironment->__set('request', $request);

$contentSearch = new ContentSearch();
$contentSearch->setEnvironment($currentEnvironment);

$queryString = false;
if (eZHTTPTool::instance()->hasGetVariable('q')) {
    $queryString = eZHTTPTool::instance()->getVariable('q');
}
if ($queryString) {
    $query = "select-fields [metadata.id as id, data.fiscal_code as fiscal_code] and fiscal_code = '{$queryString}'";
} else if ($fiscalCode) {
    $query = "fiscal_code = '\"{$fiscalCode}\"' limit 1";
} else {
    $query = 'select-fields [metadata.id as id, data.fiscal_code as fiscal_code] and fiscal_code range [*,*]';
}

$data = (array)$contentSearch->search($query, array());

header('Content-Type: application/json');
echo json_encode($data);

#eZDisplayDebug();
eZExecution::cleanExit();

