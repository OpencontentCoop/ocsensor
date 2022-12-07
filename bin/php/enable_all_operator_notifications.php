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

$options = $script->getOptions(
    '[types:]',
    '',
    [
        'types' => 'Comma separated notification types',
    ]
);
$script->initialize();
$script->setUseDebugAccumulators(true);

$user = eZUser::fetchByName('admin');
eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));
$db = eZDB::instance();

$repository = OpenPaSensorRepository::instance();
$enableNotifications = [];

if (empty($options['types'])){
    $cli->output("Set --types options with comma separated list of ");
    $allNotifications = $repository->getNotificationService()->getNotificationTypes();
    foreach ($allNotifications as $notification){
        $cli->output(' - ' . $notification->identifier);
    }
}else {
    $enableList = explode(',', $options['types']);
    foreach ($enableList as $item) {
        $notification = $repository->getNotificationService()->getNotificationByIdentifier($item);
        if ($notification) {
            $enableNotifications[] = $notification;
        } else {
            $cli->error("Notification $item not found");
        }
    }

    if (!empty($enableNotifications)) {
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

            foreach ($ops as $op) {
                $index++;
                $user = $repository->getUserService()->loadUser((int)$op->attribute('contentobject_id'));
                if ($user instanceof \Opencontent\Sensor\Api\Values\User) {
                    $cli->output("$index/$count " . $op->attribute('name'));
                    foreach ($enableNotifications as $notification) {
                        $repository->getNotificationService()->addUserToNotification($user, $notification);
                    }
                } else {
                    $cli->error("$index/$count " . $op->attribute('name'));
                }
            }
        }
    }
}


$script->shutdown();