<?php

$Module = $Params['Module'];
$Http = eZHTTPTool::instance();
$UserId = $Params['UserId'];
$Identifier = $Params['Type'];
$Transport = $Params['SubType'];

try {

    $user = eZUser::fetch($UserId);
    if (!$user instanceof eZUser) {
        throw new Exception("User $UserId not found");
    }
    $userInfo = SensorUserInfo::instance($user);

    $allNotifications = (array)SensorNotificationHelper::instance()->postNotificationTypes();
    $notifications = SensorNotificationHelper::instance()->getNotificationSubscriptionsForUser(
        $user->id(),
        $Transport ? $Transport : $userInfo->attribute('default_notification_transport')
    );

    if ($Identifier) {

        if ($Identifier == 'all' && $_SERVER['REQUEST_METHOD'] === 'POST'){

            foreach($allNotifications as $notification){
                SensorNotificationHelper::instance()->storeNotificationRules($UserId, array($notification['identifier']), $Transport);
            }
            $Identifier = null;
        }elseif ($Identifier == 'none' && $_SERVER['REQUEST_METHOD'] === 'POST'){
            foreach($allNotifications as $notification){
                SensorNotificationHelper::instance()->removeNotificationRules($UserId, array($notification['identifier']), $Transport);
            }
            $Identifier = null;
        }else {
            $exists = array_reduce($allNotifications, function ($carry, $item) use ($Identifier) {
                if ($item['identifier'] == $Identifier) {
                    $carry++;
                }

                return $carry;
            });

            if (!$exists) {
                throw new Exception("Notification identifier $Identifier not found");
            }

            $notifications = array_reduce((array)$notifications, function ($carry, $item) use ($Identifier) {
                if ($item['identifier'] == $Identifier) {
                    $carry = array($item);
                }

                return $carry;
            });

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                SensorNotificationHelper::instance()->storeNotificationRules($UserId, array($Identifier), $Transport);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                SensorNotificationHelper::instance()->removeNotificationRules($UserId, array($Identifier), $Transport);
            }
        }
    }

    $data = array();
    foreach($allNotifications as $postNotification){
        if ($Identifier && $Identifier != $postNotification['identifier']){
            continue;
        }
        $active = array_reduce((array)$notifications, function ($carry, $item) use ($postNotification) {
            if ($item['identifier'] == $postNotification['identifier']) {
                $carry++;
            }

            return $carry;
        });
        $item = $postNotification;
        $item['enabled'] = $active > 0;
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
header( 'HTTP/1.1 200 OK' );
echo json_encode( $result );

eZExecution::cleanExit();
