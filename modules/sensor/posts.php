<?php
/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$postId = $Params['ID'];
$Offset = $Params['Offset'];
if ( !is_numeric( $Offset ) )
    $Offset = 0;

if ( !is_numeric( $postId ) )
{
    $node = SensorHelper::postContainerNode();
    //$module->redirectTo( $node->attribute( 'url_alias' ) );
    $contentModule = eZModule::exists( 'content' );
    return $contentModule->run(
        'view',
        array( 'full', $node->attribute( 'node_id' ) ),
        false,
        array( 'Offset' => $Offset )
    );
}
else
{
    eZPreferences::sessionCleanup();

    try
    {
        $repository = OpenPaSensorRepository::instance();
        $post = $repository->getPostService()->loadPost( $postId );

        $action = new \OpenContent\Sensor\Api\Action\Action();
        $action->identifier = 'read';
        $repository->getActionService()->runAction( $action, $post );

        $tpl->setVariable( 'view_parameters', isset( $viewParameters ) ? $viewParameters : array() );
        $tpl->setVariable( 'post', $post );
        $tpl->setVariable( 'user', $repository->getCurrentUser() );
    }
    catch( Exception $e )
    {
        $tpl->setVariable( 'error', $e->getMessage() );
    }

    $Result = array();
    $Result['persistent_variable'] = $tpl->variable( 'persistent_variable' );
    $Result['content'] = $tpl->fetch( 'design:sensor/post/full.tpl' );
    $Result['node_id'] = 0;

    $contentInfoArray = array( 'url_alias' => 'sensor/post/' . $postId );
    $contentInfoArray['persistent_variable'] = false;
    if ( $tpl->variable( 'persistent_variable' ) !== false )
    {
        $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );
    }
    $Result['content_info'] = $contentInfoArray;
    $Result['path'] = array();
    return $Result;
}
