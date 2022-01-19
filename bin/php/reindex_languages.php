<?php

ini_set("memory_limit", "-1");
set_time_limit(0);

require 'autoload.php';

$script = eZScript::instance([
        'description' => ("Reindex all posts languages\n\n"),
        'use-session' => false,
        'use-modules' => true,
        'use-extensions' => true]
);

$script->startup();
$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators(true);
$cli = eZCLI::instance();

$def = eZContentObject::definition();
$conds = [
    'status' => eZContentObject::STATUS_PUBLISHED,
];
$count = eZPersistentObject::count($def, $conds, 'id');

$cli->output("Number of objects to index: $count");

$length = 50;
$limit = ['offset' => 0, 'length' => $length];
$script->resetIteration($count);

$searchEngine = eZSearch::getEngine();
$plugin = new SensorIndexLangBitwise();

function updateSolr($postData)
{
    $errorMessage = 'Error updating solr data';
    $solrBase = new eZSolrBase();
    $maxRetries = (int)eZINI::instance('solr.ini')->variable('SolrBase', 'ProcessMaxRetries');
    eZINI::instance('solr.ini')->setVariable('SolrBase', 'ProcessTimeout', 60);
    if ($maxRetries < 1) {
        $maxRetries = 1;
    }

    $tries = 0;
    while ($tries < $maxRetries) {
        try {
            $tries++;
            return $solrBase->sendHTTPRequest($solrBase->SearchServerURI . '/update?commit=true', json_encode($postData), 'application/json', 'OpenSegnalazioni');
        } catch (ezfSolrException $e) {
            $doRetry = false;
            $errorMessage = $e->getMessage();
            switch ($e->getCode()) {
                case ezfSolrException::REQUEST_TIMEDOUT : // Code error 28. Server is most likely overloaded
                case ezfSolrException::CONNECTION_TIMEDOUT : // Code error 7, same thing
                    $errorMessage .= ' // Retry #' . $tries;
                    $doRetry = true;
                    break;
            }

            if (!$doRetry)
                break;
        }
    }

    throw new Exception($errorMessage);
}

$solr = new eZSolr();

do {
    eZContentObject::clearCache();
    $objects = eZPersistentObject::fetchObjectList($def, null, $conds, null, $limit);
    $updateData = [];
    foreach ($objects as $object) {
        $extraFields = $plugin->getExtraFields($object);
        foreach ($extraFields as $language => $fields){
            $updateItem = [
                'meta_guid_ms' => $solr->guid((int)$object->attribute('id'), $language),
            ];
            foreach ($fields as $locale => $value){
                $updateItem[$plugin->getExtraFieldString($locale)] = ['set' => $value];
            }
            $updateData[] = $updateItem;
        }
        $script->iterate($cli, true);
    }
    updateSolr($updateData);
    $limit['offset'] += $length;

} while (count($objects) == $length);

$cli->output();
$cli->output("done");

$script->shutdown();
