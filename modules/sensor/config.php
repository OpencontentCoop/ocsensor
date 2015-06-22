<?php

$Module = $Params['Module'];
$Offset = $Params['Offset'] ? $Params['Offset'] : 0;
$Part = $Params['Part'] ? $Params['Part'] : 'users';
$tpl = eZTemplate::factory();
$viewParameters = array( 'offset' => $Offset );
$currentUser = eZUser::currentUser();

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
    $operators = SensorHelper::operators();
    $tpl->setVariable( 'operators', $operators );
}

$data = array();
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