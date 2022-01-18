<?php

ini_set("memory_limit", "-1");
set_time_limit(0);

require 'autoload.php';

$script = eZScript::instance(array(
        'description' => ("Reindex posts by participant group\n\n"),
        'use-session' => false,
        'use-modules' => true,
        'use-extensions' => true)
);

$script->startup();

$options = $script->getOptions(
    '[groups:]',
    '',
    array(
        'groups' => 'Dash separated group id list',
    )
);
$script->initialize();
$script->setUseDebugAccumulators(true);

$user = eZUser::fetchByName('admin');
eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));
$cli = eZCLI::instance();

try {
    $groups = explode('-', (string)$options['groups']);
    foreach ($groups as $groupId){
        SensorReindexer::reindexPostsByGroupId($groupId);
    }
    $script->shutdown();
} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}
