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

    $areas = SensorHelper::areas();
    $area = isset( $areas['tree'][0]['node'] ) ? $areas['tree'][0]['node'] : false;
    if ( $area instanceof eZContentObjectTreeNode )
    {
        $object = $area->object();
        /** @var eZContentObjectAttribute[] $areaDataMap */
        $areaDataMap = $object->attribute( 'data_map' );
        if ( isset( $areaDataMap['approver'] ) )
        {
            $params = array( 'attributes' => array( 'approver' => implode( '-', $objectIdList ) ) );
            $result = eZContentFunctions::updateAndPublishObject( $object, $params );
            $Module->redirectTo( '/sensor/config' );
            return;
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

$data = array();
/** @var eZContentObjectTreeNode[] $otherFolders */
$otherFolders = eZContentObjectTreeNode::subTreeByNodeID( array( 'ClassFilterType' => 'include', 'ClassFilterArray' => array( 'folder' ), 'Depth' => 1, 'DepthOperator' => 'eq', ), $root->attribute( 'node_id' ) );
foreach( $otherFolders as $folder )
{
    if ( $folder->attribute( 'contentobject_id' ) != SensorHelper::postCategoriesNode()->attribute( 'contentobject_id' ) )
    {
        $data[] = $folder;
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