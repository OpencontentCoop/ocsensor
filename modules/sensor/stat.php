<?php

$Module = $Params['Module'];
$chartIdentifier = $Params['ChartIdentifier'];
$current = $chartIdentifier ? SensorCharts::fetchChartByIdentifier( $chartIdentifier ) : false;

$tpl = eZTemplate::factory();
$tpl->setVariable( 'persistent_variable', array() );
$tpl->setVariable( 'current',$current );

$Result = array();
$Result['persistent_variable'] = $tpl->variable( 'persistent_variable' );
$Result['content'] = $tpl->fetch( 'design:sensor/stat.tpl' );
$Result['node_id'] = 0;

$contentInfoArray = array( 'url_alias' => 'sensor/stat' );
$contentInfoArray['persistent_variable'] = array();
if ( $tpl->variable( 'persistent_variable' ) !== false )
{
    $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );
}
$Result['content_info'] = $contentInfoArray;
$Result['path'] = array();
