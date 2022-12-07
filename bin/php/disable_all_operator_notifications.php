<?php

require 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance([
    'description' => (""),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true,
]);

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators(true);

$user = eZUser::fetchByName('admin');
eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));
$db = eZDB::instance();

$repository = OpenPaSensorRepository::instance();
$allNotifications = $repository->getNotificationService()->getNotificationTypes();

$count = eZContentObjectTreeNode::subTreeCountByNodeID([
    'ClassFilterArray' => ['sensor_operator'],
    'ClassFilterType' => 'include'
],1);
$cli->output("Analyze $count operators...");

$offset = 0;
$index = 0;
while ($offset < $count) {
    /** @var eZContentObjectTreeNode[] $ops */
    $ops = eZContentObjectTreeNode::subTreeByNodeID([
        'ClassFilterArray' => ['sensor_operator'],
        'ClassFilterType' => 'include',
        'Limit' => 100,
        'Offset' => $offset,
        'SortBy' => ['contentobject_id', 'asc'],
    ], 1);
    $offset += 100;

    foreach ($ops as $op){
        $index++;
        $user = $repository->getUserService()->loadUser((int)$op->attribute('contentobject_id'));
        if ($user instanceof \Opencontent\Sensor\Api\Values\User) {
            $cli->output("$index/$count " . $op->attribute('name'));
            foreach ($allNotifications as $notification) {
                $repository->getNotificationService()->removeUserToNotification($user, $notification);
            }
        }else{
            $cli->error("$index/$count " . $op->attribute('name'));
        }
    }
}


$script->shutdown();