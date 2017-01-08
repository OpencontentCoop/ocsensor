<?php
/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$hash = $Params['Hash'];

try {
    /** @var SensorHelper $helper */
    $helper = SensorHelper::instanceFromHash($hash);
    $postId = $helper->collaborationItem->DataInt1;

    eZPreferences::sessionCleanup();
    $viewParameters = array(
        'offset' => $Offset
    );
    $user = eZUser::currentUser();
    $cacheFilePath = SensorModuleFunctions::sensorPostCacheFilePath( $user, $postId, $viewParameters );
    $localVars = array( "cacheFilePath", "postId", "module", "tpl", 'viewParameters' );
    $cacheFile = eZClusterFileHandler::instance( $cacheFilePath );
    $args = compact( $localVars );

    $data = SensorModuleFunctions::sensorPostGenerate( false, $args, true );
    $Result = $data['content'];

    return $Result;
} catch (Exception $e) {

}






