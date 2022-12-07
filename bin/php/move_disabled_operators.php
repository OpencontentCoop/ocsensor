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

$count = $repository->getOperatorsRootNode()->subTreeCount([
    'ClassFilterArray' => ['sensor_operator'],
    'ClassFilterType' => 'include'
]);
$cli->output("Analyze $count operators...");

$offset = 0;
$index = 0;
while ($offset < $count) {
    /** @var eZContentObjectTreeNode[] $ops */
    $ops = $repository->getOperatorsRootNode()->subTree([
        'ClassFilterArray' => ['sensor_operator'],
        'ClassFilterType' => 'include',
        'Limit' => 100,
        'Offset' => $offset,
        'SortBy' => ['contentobject_id', 'asc'],
    ]);
    $offset += 100;

    foreach ($ops as $op){
        $index++;
        $isEnabled = $db->arrayQuery('SELECT is_enabled FROM ezuser_setting WHERE user_id = ' . (int)$op->attribute('contentobject_id'))[0]['is_enabled'] === "1";
        if ($isEnabled){
            $cli->output("$index/$count " . $op->attribute('name'));
        }else{
            $cli->warning("$index/$count " . $op->attribute('name'));
            $user = $repository->getUserService()->loadUser((int)$op->attribute('contentobject_id'));
            if ($user instanceof \Opencontent\Sensor\Api\Values\User) {
                foreach ($allNotifications as $notification) {
                    $repository->getNotificationService()->removeUserToNotification($user, $notification);
                }
            }
            eZContentObjectTreeNodeOperations::move($op->attribute('node_id'), $repository->getUserRootNode()->attribute('node_id'));
        }
    }
}


$script->shutdown();