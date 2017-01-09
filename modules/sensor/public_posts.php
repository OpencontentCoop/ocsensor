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
        array( 'Offset' => $Offset, 'Public' => true )
    );
}
else
{
    eZPreferences::sessionCleanup();
    $viewParameters = array(
        'offset' => $Offset
    );
    $user = eZUser::currentUser();
    $cacheFilePath = SensorModuleFunctions::sensorPostCacheFilePath( $user, $postId, $viewParameters );
    $localVars = array( "cacheFilePath", "postId", "module", "tpl", 'viewParameters' );
    $cacheFile = eZClusterFileHandler::instance( $cacheFilePath );
    $args = compact( $localVars );

    $data = SensorModuleFunctions::sensorPostGenerate( false, $args, 'public' );
    $Result = $data['content'];

    return $Result;
}
