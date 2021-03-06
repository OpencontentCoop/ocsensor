<?php

//
//SensorHelper::deleteCollaborationStuff( 16 );
//SensorHelper::deleteCollaborationStuff( 17 );
//
//$db = eZDB::instance();
//$db->begin();
//$res = $db->arrayQuery( "SELECT id FROM ezcollab_item WHERE data_int1 = 1841" );
//$db->commit();
//echo '<pre>';print_r($res);die();

/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();
$offset = !is_numeric( $Params['Offset'] ) ? 0 : $Params['Offset'];
$groupId = !is_numeric( $Params['Group'] ) ? false : $Params['Group'];
$export = !is_string( $Params['Export'] ) ? false : strtolower( $Params['Export'] );
$exportAll = $http->getVariable('all', false);


$selectedList = $Params['List'];

$limit = 15;

$currentUser = eZUser::currentUser();
$currentSensorUser = SensorUserInfo::current();

$notificationPrefix = SensorHelper::factory()->getSensorCollaborationHandlerTypeString() . '_';
$notificationTypes = SensorNotificationHelper::instance()->postNotificationTypes();
$searchNotificationRules = array();
foreach($notificationTypes as $type){
    $searchNotificationRules[] = $notificationPrefix . $type['identifier'];
}

$userInfo = SensorUserInfo::current();

$notifications = SensorNotificationHelper::instance()->getNotificationSubscriptionsForUser(
    $currentUser->id(),
    $userInfo->attribute('default_notification_transport')
);

$tpl->setVariable( 'current_user_has_notifications', count($notifications) > 0 );
$tpl->setVariable( 'current_user', $currentUser );
$tpl->setVariable( 'limit', $limit );
$viewParameters = array( 'offset' => $offset );
$tpl->setVariable( 'view_parameters', $viewParameters );

$access = $currentUser->hasAccessTo( 'sensor', 'manage' );
$tpl->setVariable( 'simplified_dashboard', $access['accessWord'] == 'no' );

if ( $currentUser->isAnonymous() )
{
    $module->redirectTo( 'sensor/home' );
    return;
}
else
{
    if ( $groupId )
    {
        $group = eZPersistentObject::fetchObject(
            eZCollaborationGroup::definition(),
            null,
            array( 'user_id' => eZUser::currentUserID(), 'id' => $groupId )
        );
    }
    else
    {
        $group = $currentSensorUser->sensorCollaborationGroup();
    }

    if ( $group instanceof eZCollaborationGroup )
    {
        $access = $currentUser->hasAccessTo( 'sensor', 'manage' );
        if ( $access['accessWord'] == 'no' )
        {
            $items = SensorPostFetcher::fetchAllItems( array(), $group, $limit, $offset );
            $itemsCount = SensorPostFetcher::fetchAllItemsCount( array(), $group );
            $tpl->setVariable( 'simplified_dashboard', true );
            $tpl->setVariable( 'all_items', $items );
            $tpl->setVariable( 'all_items_count', $itemsCount );
        }
        else
        {
            $listTypes = SensorHelper::availableListTypes();
            $filters = $http->hasGetVariable( 'filters' ) ? $http->getVariable( 'filters' ) : array();
            $availableFilters = array( 'id', 'subject', 'category', 'creator_id', 'creation_range', 'owner' );
            foreach( $filters as $key => $filter )
            {
                if ( !in_array( $key, $availableFilters ) || empty( $filter ) )
                {
                    unset( $filters[$key] );
                }
            }

            if ( $export )
            {
                try
                {
                    if ( $exportAll )
                    {
                        $exporter = SensorHelper::instantiateExporter($export, $filters, null, $selectedList);
                    }
                    else
                    {
                        $exporter = SensorHelper::instantiateExporter( $export, $filters, $group, $selectedList );
                    }
                    ob_get_clean(); //chiudo l'ob_start dell'index.php
                    $exporter->handleDownload();
                    eZExecution::cleanExit();
                }
                catch( Exception $e )
                {
                    $module->redirectTo( 'sensor/home' );
                    return;
                }
            }
            else
            {

                $filtersQuery = count( $filters ) > 0 ? '?' . http_build_query( array( 'filters' => $filters ) ) : '';
                $currentList = false;
                foreach( $listTypes as $key => $type )
                {
                    $count = call_user_func( $type['count_function'], $filters, $group );
                    $listTypes[$key]['count'] = call_user_func( $type['count_function'], $filters, $group );
                    if ( $selectedList == $type['identifier'] || ( !$selectedList && $count > 0 && $currentList == false ) )
                    {
                        $currentList = $listTypes[$key];
                    }
                }

                if ( $currentList == false )
                {
                    $currentList = $listTypes[0];
                }

                $items = call_user_func( $currentList['list_function'], $filters, $group, $limit, $offset );
                $expiringItems = SensorPostFetcher::fetchExpiringItems( array(), $group, 100 );

                $tpl->setVariable( 'items', $items );
                $tpl->setVariable( 'expiring_items', $expiringItems );
                $tpl->setVariable( 'filters', $filters );
                $tpl->setVariable( 'filters_query', $filtersQuery );
                $tpl->setVariable( 'simplified_dashboard', false );
                $tpl->setVariable( 'current_list', $currentList );
                $tpl->setVariable( 'list_types', $listTypes );
            }
        }
    }
    else
    {
        $module->redirectTo( 'sensor/home' );
        return;
    }

    $Result = array();
    
    $Result['persistent_variable'] = $tpl->variable( 'persistent_variable' );
    $Result['content'] = $tpl->fetch( 'design:sensor/dashboard.tpl' );
    $Result['node_id'] = 0;
    
    $contentInfoArray = array( 'url_alias' => 'sensor/home' );
    $contentInfoArray['persistent_variable'] = false;
    if ( $tpl->variable( 'persistent_variable' ) !== false )
    {
        $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );
    }
    $Result['content_info'] = $contentInfoArray;
    $Result['path'] = array();
}
