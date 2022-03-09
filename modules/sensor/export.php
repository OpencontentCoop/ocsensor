<?php

/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();

$repository = OpenPaSensorRepository::instance();

$http->setGetVariable('executionTimes', true);
$http->setGetVariable('readingStatuses', true);
$http->setGetVariable('capabilities', true);

$http->setGetVariable('currentUserInParticipants', false);
$http->setGetVariable('q', 'sort [modified=>desc]');
$http->setGetVariable('format', 'json');
if ($http->hasGetVariable('source') && $http->getVariable('source') === 'posts'){
    $http->setGetVariable('ignorePolicies', false);
}else {
    $http->setGetVariable('ignorePolicies', true);
}

$export = new SensorPostCsvExporter($repository);
try{
    $export->handleDownload();
    eZExecution::cleanExit();

}catch (Exception $e){
    $repository->getUserService()->addAlert($repository->getCurrentUser(), $e->getMessage(), 'error');
    $module->redirectTo('/sensor/stat');
    return;
}
