<?php

$Module = $Params['Module'];
$Http = eZHTTPTool::instance();
$UserId = $Params['UserId'];
$Identifier = $Params['Type'];

try {

    $repository = OpenPaSensorRepository::instance();

    $user = $repository->getUserService()->loadUser($UserId);
    if (!$user instanceof \Opencontent\Sensor\Api\Values\User) {
        throw new Exception("User $UserId not found");
    }

    $allNotifications = $repository->getNotificationService()->getNotificationTypes();
    $userNotifications = $repository->getNotificationService()->getUserNotifications($user);

    if ($Identifier) {

        if ($Identifier == 'all' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($allNotifications as $notification) {
                $repository->getNotificationService()->addUserToNotification($user, $notification);
            }
            $Identifier = null;

        } elseif ($Identifier == 'none' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($allNotifications as $notification) {
                $repository->getNotificationService()->removeUserToNotification($user, $notification);
            }
            $Identifier = null;

        } else {

            $notification = $repository->getNotificationService()->getNotificationByIdentifier($Identifier);
            if (!$notification instanceof \Opencontent\Sensor\Api\Values\NotificationType) {
                throw new Exception("Notification identifier $Identifier not found");
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $repository->getNotificationService()->addUserToNotification($user, $notification);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                $repository->getNotificationService()->removeUserToNotification($user, $notification);
            }
        }
    }

    $data = array();
    foreach ($allNotifications as $notification) {
        $item = $notification->jsonSerialize();
        $item['enabled'] = in_array($notification->identifier, $userNotifications);
        $data[] = $item;
    }

    $result = array(
        'result' => 'success',
        'message' => 'Ok',
        'data' => $data
    );

} catch (Exception $e) {

    $result = array(
        'result' => 'error',
        'message' => $e->getMessage(),
        'data' => null
    );
}

header('Content-Type: application/json');
header('HTTP/1.1 200 OK');
echo json_encode($result);

eZExecution::cleanExit();
