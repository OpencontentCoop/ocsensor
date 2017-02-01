<?php
/** @var eZModule $module */
$module = $Params['Module'];
$contentType = eZHTTPTool::instance()->getVariable( 'contentType', 'geojson' );
$data = array();

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
    elseif ( $contentType == 'operators' )
    {
        $query = eZHTTPTool::instance()->getVariable( 'q', null );
        $postId = eZHTTPTool::instance()->getVariable( 'post_id', 0 );
        $value = eZHTTPTool::instance()->getVariable( 'value', null );
        $offset = eZHTTPTool::instance()->getVariable( 'page', 0 ) * 30;

        $params = array(
            'raw_result' => true,
            'limit' => 30,
            'offset' => $offset
        );
        if ( $query )
            $params['query'] = $query;

        $result = array(
            'SearchCount' => 0,
            'SearchResult' => array()
        );

        if ( $postId > 0 )
        {
            try
            {
                $post = SensorHelper::instanceFromContentObjectId( $postId );
                if ( $value == 'operators' )
                {
                    $result = $post->operators( $post->currentSensorPost, $params );
                }
                else
                {
                    $result = $post->observers( $post->currentSensorPost, $params );
                }
            }
            catch ( Exception $e )
            {
                $data['error'] = $e->getMessage();
            }
        }
        else
        {
            $result = SensorHelper::operators( null, $params );
        }

        $data['total_count'] = $result['SearchCount'];
        $data['items'] = array();

        /** @var eZFindResultNode $operator */
        foreach( $result['SearchResult'] as $operator )
        {
            if ( intval( $operator->attribute( 'contentobject_id' ) ) > 0 )
            {
                $tpl = eZTemplate::factory();
                $tpl->resetVariables();
                $tpl->setVariable( 'sensor_person', $operator->attribute( 'object' ) );
                $data['items'][] = array(
                    'id' => $operator->attribute( 'contentobject_id' ),
                    'text' => strip_tags( $tpl->fetch( 'design:content/view/sensor_person.tpl' ) )
                );
            }
        }
    }
    elseif ( $contentType == 'chart' )
    {
        $charts = new SensorCharts( eZHTTPTool::instance()->getVariable( 'parameters' ) );
        $data = $charts->getData();
    }

    if (eZHTTPTool::instance()->hasGetVariable( 'debug')){
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        eZDisplayDebug();
        eZExecution::cleanExit();
    }

    header('Content-Type: application/json');
    header( 'HTTP/1.1 200 OK' );
    echo json_encode( $data );
}
catch ( Exception $e )
{
    header('Content-Type: application/json');
    header( 'HTTP/1.1 500 Internal Server Error' );
    $data = array( 'error' => $e->getMessage() );
    echo json_encode( $data );
}
eZExecution::cleanExit();