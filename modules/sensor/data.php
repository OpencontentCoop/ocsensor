<?php
/** @var eZModule $module */
$module = $Params['Module'];
$contentType = eZHTTPTool::instance()->getVariable( 'contentType', 'geojson' );
$data = array();
header('Content-Type: application/json');
try
{
    if ( $contentType == 'geojson' )
    {
        $data = SensorHelper::fetchSensorGeoJsonFeatureCollection();
    }
    elseif ( $contentType == 'marker' )
    {
        $postId = eZHTTPTool::instance()->getVariable( 'id', $contentType );
        $cacheFilePath = SensorModuleFunctions::sensorPostCacheFilePath( null, $postId, array(), 'popup' );
        $cacheFile = eZClusterFileHandler::instance( $cacheFilePath );
        $ini = eZINI::instance();
        $viewCacheEnabled = ( $ini->variable( 'ContentSettings', 'ViewCaching' ) == 'enabled' );
        if ( $viewCacheEnabled )
        {
            $Result = $cacheFile->processCache( array( 'SensorModuleFunctions', 'sensorCacheRetrieve' ),
                array( 'SensorModuleFunctions', 'sensorPostPopupGenerate' ),
                null,
                null,
                $postId );
        }
        else
        {
            $data = SensorModuleFunctions::sensorPostPopupGenerate( false, $postId );
            $Result = $data['content'];
        }
        $data = array( 'content' => $Result );
    }

    header( 'HTTP/1.1 200 OK' );
    echo json_encode( $data );
}
catch ( Exception $e )
{
    header( 'HTTP/1.1 500 Internal Server Error' );
    $data = array( 'error' => $e->getMessage() );
    echo json_encode( $data );
}
eZExecution::cleanExit();