<?php

ini_set("memory_limit", "-1");
set_time_limit(0);

require 'autoload.php';

$script = eZScript::instance([
        'description' => ("Reindex all posts\n\n"),
        'use-session' => false,
        'use-modules' => true,
        'use-extensions' => true,
    ]
);

$script->startup();

$options = $script->getOptions(
    '[id:]',
    '',
    array(
        'id' => 'Filter by post id',
    )
);
$script->initialize();
$script->setUseDebugAccumulators(true);

$user = eZUser::fetchByName('admin');
eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));
$cli = eZCLI::instance();

try {
    $repository = OpenPaSensorRepository::instance();
    if ($options['id']){
        $objects = [eZContentObject::fetch((int)$options['id'])];
    }else {
        $cli->output('Fetching objects... ', false);
        $conditions = [
            'contentclass_id' => $repository->getPostContentClass()->attribute('id'),
            'status' => eZContentObject::STATUS_PUBLISHED,
        ];
        $objects = eZPersistentObject::fetchObjectList(
            eZContentObject::definition(),
            ['id', 'published'],
            $conditions,
            ['published' => 'asc']
        );
    }
    $objectsCount = count($objects);
    $cli->warning($objectsCount);

    $output = new ezcConsoleOutput();
    $progressBarOptions = ['emptyChar' => ' ', 'barChar' => '='];
    $progressBar = new ezcConsoleProgressbar($output, $objectsCount, $progressBarOptions);
    $progressBar->start();
    foreach ($objects as $index => $object) {
        try {
            $post = $repository->getPostService()->loadPost($object->attribute('id'));
            SensorTimelinePersistentObject::createOnPublishNewPost($post);
            foreach ($post->timelineItems->messages as $timelineItem) {
                SensorTimelinePersistentObject::createOnNewTimelineItem($post, $timelineItem);
            }
        } catch (Exception $e) {
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
