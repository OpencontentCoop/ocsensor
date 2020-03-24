<?php

/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();

$currentUser = eZUser::currentUser();

if ( $currentUser->isAnonymous() )
{
    $module->redirectTo( 'sensor/home' );
    return;
}
else
{
    $repository = OpenPaSensorRepository::instance();
    $currentSensorUser = SensorUserInfo::current();
    if (isset($Params['UserParameters']['export'])){
        $export = new SensorPostCsvExporter($repository);
        try{
            $export->handleDownload();
            eZExecution::cleanExit();

        }catch (Exception $e){
            $currentSensorUser->addFlashAlert($e->getMessage(), 'error');
            $module->redirectTo('/sensor/dashboard');
            return;
        }
    }

    $notifications = $repository->getNotificationService()->getUserNotifications($repository->getCurrentUser());

    $tpl->setVariable( 'current_user_has_notifications', count($notifications) > 0 );
    $tpl->setVariable( 'current_user', $currentUser );

    $access = $currentUser->hasAccessTo( 'sensor', 'manage' );
    $tpl->setVariable( 'simplified_dashboard', $access['accessWord'] == 'no' );

    $tpl->setVariable('areas', json_encode($repository->getAreasTree()));
    $tpl->setVariable('categories', json_encode($repository->getCategoriesTree()));
    $tpl->setVariable('operators', json_encode($repository->getOperatorsTree()));
    $tpl->setVariable('groups', json_encode($repository->getGroupsTree()));
    $tpl->setVariable('settings', json_encode($repository->getSensorSettings()));

    $Result = array();

    $Result['persistent_variable'] = $tpl->variable('persistent_variable');
    $Result['content'] = $tpl->fetch('design:sensor_api_gui/dashboard.tpl');
    $Result['node_id'] = 0;

    $contentInfoArray = array('url_alias' => 'sensor/home');
    $contentInfoArray['persistent_variable'] = false;
    if ($tpl->variable('persistent_variable') !== false) {
        $contentInfoArray['persistent_variable'] = $tpl->variable('persistent_variable');
    }
    $Result['content_info'] = $contentInfoArray;
    $Result['path'] = array();
}
