<?php

ini_set("memory_limit", "-1");
set_time_limit(0);

require 'autoload.php';

$script = eZScript::instance(array(
        'description' => ("Reindex all posts\n\n"),
        'use-session' => false,
        'use-modules' => true,
        'use-extensions' => true)
);

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators(true);

$user = eZUser::fetchByName('admin');
eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));
$cli = eZCLI::instance();

try {

    $repository = OpenPaSensorRepository::instance();
    $cli->output('Fetching objects... ', false);
    $conditions = ['contentclass_id' => $repository->getPostContentClass()->attribute('id')];
    $objects = eZPersistentObject::fetchObjectList(
        eZContentObject::definition(),
        ['id', 'published'],
        $conditions,
        ['published' => 'asc']
    );
    $objectsCount = count($objects);
    $cli->warning($objectsCount);

    $output = new ezcConsoleOutput();
    $progressBarOptions = array('emptyChar' => ' ', 'barChar' => '=');
    $progressBar = new ezcConsoleProgressbar($output, $objectsCount, $progressBarOptions);
    $progressBar->start();
    foreach ($objects as $object) {
        try {
            $post = $repository->getPostService()->loadPost($object->attribute('id'));
            SensorTimelineIndexer::indexPublish($post);
            foreach ($post->timelineItems->messages as $timelineItem) {
                SensorTimelineIndexer::indexTimelineItem($post, $timelineItem);
            }
        }catch (Exception $e){

        }
        eZContentObject::clearCache();
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
