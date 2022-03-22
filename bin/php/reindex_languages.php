<?php

use Opencontent\Sensor\Legacy\SearchService\SolrMapper;

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
    SolrMapper::patchSearchIndex($updateData);
    $limit['offset'] += $length;

} while (count($objects) == $length);

$cli->output();
$cli->output("done");

$script->shutdown();
