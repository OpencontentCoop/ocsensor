<?php
/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$repository = OpenPaSensorRepository::instance();
$http = eZHTTPTool::instance();
$handler = new SensorCriticalPosts();
$api = $Params['api'];

if ($http->hasPostVariable('StoreFilters')){
    $changed = $handler->storeRules($_POST['Rules'], $_POST['Sql']);
    header('Content-Type: application/json');
    header( 'HTTP/1.1 200 OK' );
    echo json_encode($changed);
    eZExecution::cleanExit();
}

if ($api === 'api'){
    $data = $handler->find($_GET['p']);
    header('Content-Type: application/json');
    header( 'HTTP/1.1 200 OK' );
    echo json_encode($data);
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
    echo json_encode([
        'rules' => $handler->getRules(),
        'sql' => $handler->getSql(),
    ]);
    eZExecution::cleanExit();
}


$tpl->setVariable('filters', json_encode($handler->getFilters()));
$tpl->setVariable('rules', json_encode($handler->getRules()));

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