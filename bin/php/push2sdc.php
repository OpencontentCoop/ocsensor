<?php

ini_set("memory_limit", "-1");
set_time_limit(0);

require 'autoload.php';

$script = eZScript::instance([
        'description' => ("Push all posts to sdc\n\n"),
        'use-session' => false,
        'use-modules' => true,
        'use-extensions' => true,
    ]
);

$script->startup();

$options = $script->getOptions(
    '[id:][only-closed][slack-endpoint:][show-config][env][dry-run]',
    '',
    [
        'id' => 'Filter by post id',
        'only-closed' => 'Push only closed',
        'env' => 'env (prod o dev)',
        'force' => 'Force repush'
    ]
);
$script->initialize();
$script->setUseDebugAccumulators(true);

$startTotalTime = time();

/** @var eZUser $user */
$user = eZUser::fetchByName('admin');
eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));

$pusher = new \Opencontent\Sensor\Inefficiency\Pusher($options);
try {
    $pusher->run();
} catch (Throwable $e) {
    eZCLI::instance()->error($e->getMessage());
}


$script->shutdown();