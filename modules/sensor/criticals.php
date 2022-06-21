<?php

/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$repository = OpenPaSensorRepository::instance();
$http = eZHTTPTool::instance();
$handler = new SensorCriticalPosts();
$timelineListener = new SensorTimelineListener();
if (!$timelineListener->isEnabled()){
    header('Content-Type: application/json');
    header( 'HTTP/1.1 404 Not found' );
    echo json_encode(['error' => 'CollectSensorTimelineItems is disabled']);
    eZExecution::cleanExit();
}

$api = $Params['api'];

if ($http->hasPostVariable('UpdateData')){
    $handler->updateView();
    header('Content-Type: application/json');
    header( 'HTTP/1.1 200 OK' );
    echo json_encode(true);
    eZExecution::cleanExit();
}

if ($http->hasPostVariable('StoreFilters')){
    $handler->storeRules($_POST['Rules'], $_POST['Sql'], $_POST['PresetName']);
    header('Content-Type: application/json');
    header( 'HTTP/1.1 200 OK' );
    echo json_encode($handler->getAllRulesAndSql());
    eZExecution::cleanExit();
}

if ($api === 'reset'){
    $handler->resetRulesAndQuery();
    $module->redirectTo('sensor/criticals');
    return;
}

if ($api === 'preset'){
    $handler->setPreset($_POST['PresetName']);
    header('Content-Type: application/json');
    header( 'HTTP/1.1 200 OK' );
    $data = $handler->getAllRulesAndSql();
    echo json_encode($data);
    eZExecution::cleanExit();
    return;
}

if ($api === 'remove-preset'){
    $data = $handler->removePreset($_POST['PresetName']);
    header('Content-Type: application/json');
    header( 'HTTP/1.1 200 OK' );
    echo json_encode($data);
    eZExecution::cleanExit();
    return;
}

if ($api === 'api'){
    $data = $handler->find($_GET['p'], $_GET['latest_group'], $_GET['references']);
    header('Content-Type: application/json');
    header( 'HTTP/1.1 200 OK' );
    echo json_encode($data);
    eZExecution::cleanExit();
}

if ($api === 'csv-export'){
    $currentPreset = $handler->getCurrentPreset();
    $filename = $currentPreset . '.csv';
    header( 'X-Powered-By: eZ Publish' );
    header( 'Content-Description: File Transfer' );
    header( 'Content-Type: text/csv; charset=utf-8' );
    header( "Content-Disposition: attachment; filename=$filename" );
    header( "Pragma: no-cache" );
    header( "Expires: 0" );
    ob_get_clean();
    $output = fopen('php://output', 'w');
    $runOnce = false;
    $items = $handler->findAll();
    foreach ($items as $values) {
        if (!$runOnce) {
            fputcsv(
                $output,
                array_keys($values)
            );
            $runOnce = true;
        }
        fputcsv($output, array_values($values));
        flush();
    }
    eZExecution::cleanExit();
}

if ($api === 'query'){
    $query = $handler->getQuery();
    $query = preg_replace('/[ \t]+/', ' ', preg_replace('/[\r\n]+/', " ", $query));
    header( 'HTTP/1.1 200 OK' );
    echo '<pre style="white-space: unset;">' . $query . '</pre>';
    eZExecution::cleanExit();
}

if ($api === 'rules'){
    header('Content-Type: application/json');
    header( 'HTTP/1.1 200 OK' );
    $data = $handler->getAllRulesAndSql();
    if (isset($data['sql']['presets'])){
        $data['sql']['presets'] = array_values($data['sql']['presets']);
    }
    echo json_encode($data);
    eZExecution::cleanExit();
}

$tpl->setVariable('filters', json_encode($handler->getFilters()));
$tpl->setVariable('rules', json_encode($handler->getRules()));
$tpl->setVariable('has_sql', !empty($handler->getSql()));
$tpl->setVariable('current_preset', $handler->getCurrentPreset());
$tpl->setVariable('presets', $handler->getPresets());

$Result = [];
$Result['persistent_variable'] = $tpl->variable('persistent_variable');
$Result['content'] = $tpl->fetch('design:sensor/criticals.tpl');
$Result['node_id'] = 0;

$contentInfoArray = ['url_alias' => 'sensor/criticals'];
$contentInfoArray['persistent_variable'] = false;
if ($tpl->variable('persistent_variable') !== false) {
    $contentInfoArray['persistent_variable'] = $tpl->variable('persistent_variable');
}
$Result['content_info'] = $contentInfoArray;
$Result['path'] = [];