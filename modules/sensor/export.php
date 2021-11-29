<?php

/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();

$http->setGetVariable('executionTimes', true);
$http->setGetVariable('readingStatuses', true);
$http->setGetVariable('capabilities', true);
$http->setGetVariable('currentUserInParticipants', false);
$http->setGetVariable('q', 'sort [modified=>desc]');
$http->setGetVariable('format', 'json');
$http->setGetVariable('ignorePolicies', true);

$repository = OpenPaSensorRepository::instance();
$export = new SensorPostCsvExporter($repository);
try{
    $export->handleDownload();
    eZExecution::cleanExit();

}catch (Exception $e){
    $repository->getUserService()->addAlert($repository->getCurrentUser(), $e->getMessage(), 'error');
    $module->redirectTo('/sensor/stat');
    return;
}
