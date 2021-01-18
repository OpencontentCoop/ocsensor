<?php

$tpl = eZTemplate::factory();

$Result = array();
$Result['persistent_variable'] = $tpl->variable('persistent_variable');
$Result['content'] = $tpl->fetch('design:sensor_api_gui/inbox.tpl');
$Result['node_id'] = 0;

$contentInfoArray = array('url_alias' => 'sensor/inbox');
$contentInfoArray['persistent_variable'] = array();
if ($tpl->variable('persistent_variable') !== false) {
    $contentInfoArray['persistent_variable'] = $tpl->variable('persistent_variable');
}
$Result['content_info'] = $contentInfoArray;
$Result['path'] = array();
