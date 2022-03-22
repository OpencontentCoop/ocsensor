<?php

use Opencontent\Sensor\Legacy\SearchService\SolrMapper;

ini_set("memory_limit", "-1");
set_time_limit(0);

require 'autoload.php';

$script = eZScript::instance(array(
        'description' => ("Reindex posts authors\n\n"),
        'use-session' => false,
        'use-modules' => true,
        'use-extensions' => true)
);

$script->startup();

$options = $script->getOptions(
    '[index:]',
    '',
    array(
        'index' => 'Skip to index',
    )
);
$script->initialize();
$script->setUseDebugAccumulators(true);

$user = eZUser::fetchByName('admin');
eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));
$cli = eZCLI::instance();

try {

    $repository = OpenPaSensorRepository::instance();
    $solr = new eZSolr();
    $cli->output('Fetching objects... ', false);
    $conditions = ['contentclass_id' => $repository->getPostContentClass()->attribute('id')];
    if ($options['user']){
        $conditions['owner_id'] = (int)$options['user'];
    }
    $objects = eZPersistentObject::fetchObjectList(
        eZContentObject::definition(),
        null,
        $conditions,
        ['published' => 'desc']
    );
    $objectsCount = count($objects);
    $cli->warning($objectsCount);

    $output = new ezcConsoleOutput();
    $progressBarOptions = array('emptyChar' => ' ', 'barChar' => '=');
    $progressBar = new ezcConsoleProgressbar($output, $objectsCount, $progressBarOptions);
    $progressBar->start();
    $updateData = [];
    $skipTo = (int)$options['index'];
    foreach ($objects as $index => $object) {
        if ($index > $skipTo) {
            try {
                $owner = $object->attribute('owner');
                $groupListString = '0';
                if ($owner instanceof eZContentObject && $owner->attribute('class_identifier') == 'user') {
                    $ezUser = eZUser::fetch($owner->attribute('id'));
                    $idList = $ezUser->groups();
                    $idList = array_map('intval', $idList);
                    $idList = array_diff($idList, [4, 11, 12, 42]);
                    $groupListString = !empty($idList) ? implode(',', array_unique($idList)) : '0';
                }
                $updateData[] = [
                    'meta_guid_ms' => $solr->guid((int)$object->attribute('id'), eZLocale::currentLocaleCode()),
                    'sensor_author_group_list_lk' => [
                        'set' => $groupListString,
                    ]
                ];
            } catch (Exception $e) {

            }
            eZContentObject::clearCache();

            if (count($updateData) >= 20) {
                SolrMapper::patchSearchIndex(json_encode($updateData));
                $updateData = [];
            }
        }
        $progressBar->advance();
    }

    if (count($updateData) > 0){
        SolrMapper::patchSearchIndex(json_encode($updateData));
        $updateData = [];
    }

    $progressBar->finish();
    $cli->output();

    $script->shutdown();
} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}
