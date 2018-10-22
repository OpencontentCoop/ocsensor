<?php

/** @var eZModule $Module */
$Module = $Params['Module'];
$Http = eZHTTPTool::instance();
$Offset = $Params['Offset'] ? $Params['Offset'] : 0;
$Part = $Params['Part'] ? $Params['Part'] : 'users';
$tpl = eZTemplate::factory();
$viewParameters = array( 'offset' => $Offset, 'query' => null );
$currentUser = eZUser::currentUser();

if ( $Http->hasVariable( 's' ) )
    $viewParameters['query'] = $Http->variable( 's' );

if ( $Http->hasPostVariable( 'SelectDefaultApprover' ) )
{
    eZContentBrowse::browse( array( 'action_name' => 'SelectDefaultApprover',
                                    'return_type' => 'ObjectID',
                                    'class_array' => eZUser::fetchUserClassNames(),
                                    'start_node' => SensorHelper::operatorsNode()->attribute( 'node_id' ),
                                    'cancel_page' => '/sensor/config',
                                    'from_page' => '/sensor/config' ), $Module );
    return;
}

if ( $Http->hasPostVariable( 'BrowseActionName' )
     && $Http->postVariable( 'BrowseActionName' ) == 'SelectDefaultApprover' )
{
    $objectIdList = $Http->postVariable( 'SelectedObjectIDArray' );
    if ( !empty( $objectIdList ) ) {
        $areas = SensorHelper::areas();
        $area = isset($areas['tree'][0]['node']) ? $areas['tree'][0]['node'] : false;
        if ($area instanceof eZContentObjectTreeNode) {
            $object = $area->object();
            /** @var eZContentObjectAttribute[] $areaDataMap */
            $areaDataMap = $object->attribute('data_map');
            if (isset($areaDataMap['approver'])) {
                $params = array('attributes' => array('approver' => implode('-', $objectIdList)));
                $result = eZContentFunctions::updateAndPublishObject($object, $params);
                $Module->redirectTo('/sensor/config');

                return;
            }
        }
    }
}

//AddOperatorLocation
if ( $Http->hasPostVariable( 'AddOperatorLocation' ) )
{
    eZContentBrowse::browse( array( 'action_name' => 'AddOperatorLocation',
                                    'return_type' => 'NodeID',
                                    'class_array' => eZUser::fetchUserClassNames(),
                                    'start_node' => eZINI::instance('content.ini')->variable('NodeSettings','UserRootNode'),
                                    'cancel_page' => '/sensor/config/operators',
                                    'from_page' => '/sensor/config/operators' ), $Module );
    return;
}

if ( $Http->hasPostVariable( 'BrowseActionName' )
     && $Http->postVariable( 'BrowseActionName' ) == 'AddOperatorLocation' )
{
    $nodeIdList = $Http->postVariable( 'SelectedNodeIDArray' );

    $operatorsNode = SensorHelper::operatorsNode();
    foreach( $nodeIdList as $nodeId )
    {
        $node = eZContentObjectTreeNode::fetch($nodeId);
        if ($node instanceof eZContentObjectTreeNode){
            eZContentOperationCollection::addAssignment($nodeId, $node->attribute( 'contentobject_id' ), array($operatorsNode->attribute('node_id')));
        }
    }
    $Module->redirectTo( '/sensor/config/operators' );
    return;
}


$root = SensorHelper::rootNode();

if ( $Part == 'areas' )
{
    $areas = SensorHelper::areas();
    $tpl->setVariable( 'areas', $areas['tree'] );
}

elseif ( $Part == 'users' )
{
    $usersParentNode = eZContentObjectTreeNode::fetch( intval( eZINI::instance()->variable( "UserSettings", "DefaultUserPlacement" ) ) );
    $tpl->setVariable( 'user_parent_node', $usersParentNode );
}

elseif ( $Part == 'categories' )
{
    $categories = SensorHelper::categories();
    $tpl->setVariable( 'categories', $categories['tree'] );
}

elseif ( $Part == 'operators' )
{
    $tpl->setVariable( 'operator_parent_node', SensorHelper::operatorsNode() );
    $tpl->setVariable( 'operator_class', eZContentClass::fetchByIdentifier( 'sensor_operator' ) );
}

elseif ( $Part == 'notifications' )
{
    if ( $Http->hasPostVariable( 'StoreNotificationsText' ) )
    {        
        $data = $Http->postVariable( 'NotificationsText' );
        SensorNotificationTextHelper::storeTexts($data);
    }
    if ( $Http->hasPostVariable( 'ResetNotificationsText' ) )
    {                
        SensorNotificationTextHelper::reset();
    }
    $texts = SensorNotificationTextHelper::getTexts();
    $tpl->setVariable( 'notification_types', SensorNotificationHelper::instance()->postNotificationTypes() );
    $tpl->setVariable( 'participant_roles', SensorPost::participantRoleNameMap() );
    
    $allLanguages = eZContentLanguage::fetchList();
    $allLanguageLocales = eZContentLanguage::fetchLocaleList();
    $siteaccessList = array_column(eZSiteAccess::siteaccessList(), 'name');
    $languages = array();
    $ini = eZINI::instance();
    $locale = eZLocale::instance();
    $host = eZINI::instance()->variable( 'SiteSettings', 'SiteURL' );        
    
    $currentLanguage = array(
        $locale->localeCode() => array(
            'url' => '//' . $host,
            'name' => $locale->languageName(),
            'locale' => $locale->localeCode()
        )
    );    
    
    if ( $ini->hasVariable( 'RegionalSettings', 'TranslationSA' ) && count($ini->variable( 'RegionalSettings', 'TranslationSA' )) > 0 )
    {
        $translationSiteAccesses = $ini->variable( 'RegionalSettings', 'TranslationSA' );
        foreach ( $translationSiteAccesses as $siteAccessName => $translationName )
        {            
            if (in_array($siteAccessName, $siteaccessList)){
                $host = eZSiteAccess::getIni( $siteAccessName )->variable( 'SiteSettings', 'SiteURL' );            
                $locale = eZSiteAccess::getIni( $siteAccessName )->variable( 'RegionalSettings', 'ContentObjectLocale' );
                if(in_array($locale, $allLanguageLocales)){
                    $languages[$locale] = array(
                        'url' => '//' . $host,
                        'name' => $translationName,
                        'locale' => $locale
                    );
                }
            }
        }
    }
    if(empty($languages)){
        $languages = $currentLanguage;
    }

    $tpl->setVariable( 'languages', $languages );  
    $tpl->setVariable( 'all_languages', $allLanguages );  
    $tpl->setVariable( 'texts', $texts );
    $samplePost = SensorHelper::postContainerNode()->subtree(array(
        'Limit' => 1,
        'ClassFilterType' => 'include',
        'ClassFilterInclude' => array( 'sensor_post' ),
        'SortBy' => array( 'published', false )
    ));
    $tpl->setVariable( 'sample_post_id', $samplePost[0] instanceof eZContentObjectTreeNode ? $samplePost[0]->attribute( 'contentobject_id' ) : 0 );
}

$data = array();
/** @var eZContentObjectTreeNode[] $otherFolders */
$otherFolders = (array)eZContentObjectTreeNode::subTreeByNodeID( array( 'ClassFilterType' => 'include', 'ClassFilterArray' => array( 'folder' ), 'Depth' => 1, 'DepthOperator' => 'eq', ), $root->attribute( 'node_id' ) );
foreach( $otherFolders as $folder )
{
    if ( $folder->attribute( 'contentobject_id' ) != SensorHelper::postCategoriesNode()->attribute( 'contentobject_id' ) )
    {
        $data[] = $folder;
    }
}

$ids = SensorHelper::defaultApproverIdArray();
$adminRole = eZRole::fetchByName( "Sensor Admin" );
if ( $adminRole instanceof eZRole )
{
    foreach($ids as $id){
        $adminRole->assignToUser($id);
    }
}

$tpl->setVariable( 'view_parameters', $viewParameters );
$tpl->setVariable( 'current_part', $Part );
$tpl->setVariable( 'data', $data );
$tpl->setVariable( 'root', $root );
$tpl->setVariable( 'current_user', $currentUser );
$tpl->setVariable( 'persistent_variable', array() );

$Result = array();
$Result['persistent_variable'] = $tpl->variable( 'persistent_variable' );
$Result['content'] = $tpl->fetch( 'design:sensor/config.tpl' );
$Result['node_id'] = 0;

$contentInfoArray = array( 'url_alias' => 'sensor/config' );
$contentInfoArray['persistent_variable'] = false;
if ( $tpl->variable( 'persistent_variable' ) !== false )
{
    $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );
}
$Result['content_info'] = $contentInfoArray;
$Result['path'] = array();
