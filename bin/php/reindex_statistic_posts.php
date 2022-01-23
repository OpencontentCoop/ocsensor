<?php

ini_set("memory_limit", "-1");
set_time_limit(0);

require 'autoload.php';

use Opencontent\Sensor\Legacy\SearchService\SolrMapper;

$script = eZScript::instance(array(
        'description' => ("Reindex all posts\n\n"),
        'use-session' => false,
        'use-modules' => true,
        'use-extensions' => true)
);

$script->startup();

$options = $script->getOptions(
    '[user:]',
    '',
    array(
        'user' => 'Filter by user id',
    )
);
$script->initialize();
$script->setUseDebugAccumulators(true);

$user = eZUser::fetchByName('admin');
eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));
$cli = eZCLI::instance();

try {

    $repository = OpenPaSensorRepository::instance();
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
    foreach ($objects as $object) {
        ezfIndexSensor::indexStatisticPost($object);
        $progressBar->advance();
    }
    $progressBar->finish();
    $cli->output();

    $script->shutdown();
} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}
