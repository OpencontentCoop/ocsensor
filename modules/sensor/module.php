<?php
$Module = array('name' => 'Sensor');

$ViewList = array();
$ViewList['home'] = array(
    'script' => 'home.php',
    'functions' => array('use')
);

$ViewList['info'] = array(
    'script' => 'info.php',
    'params' => array('Page'),
    'functions' => array('use')
);

$ViewList['posts'] = array(
    'script' => 'posts.php',
    'params' => array('ID', 'Offset'),
    'functions' => array('use')
);

$ViewList['add'] = array(
    'script' => 'add.php',
    'params' => array(),
    'functions' => array('use')
);

$ViewList['copy'] = array(
    'script' => 'copy.php',
    'params' => array('Id'),
    'functions' => array('use')
);

$ViewList['edit'] = array(
    'script' => 'edit.php',
    'params' => array('ID'),
    'functions' => array('use')
);

$ViewList['dashboard'] = array(
    'script' => 'dashboard.php',
    'params' => array("Part", "Group", "Export"),
    'unordered_params' => array(
        "list" => "List",
        "offset" => "Offset"
    ),
    'functions' => array('use')
);

$ViewList['redirect'] = array(
    'script' => 'redirect.php',
    'params' => array('View'),
    'functions' => array('use')
);

$ViewList['test_mail'] = array(
    'script' => 'test_mail.php',
    'params' => array('Type', 'Id', 'Param', 'Param2'),
    'functions' => array('debug')
);

$ViewList['config'] = array(
    'script' => 'config.php',
    'params' => array("Part"),
    'unordered_params' => array('offset' => 'Offset'),
    'functions' => array('config')
);

$ViewList['notifications'] = array(
    'script' => 'notifications.php',
    'params' => array('UserId', 'Type', 'SubType'),
    'functions' => array('config')
);

$ViewList['stat'] = array(
    'script' => 'stat.php',
    'params' => array('ChartIdentifier'),
    'unordered_params' => array(),
    'functions' => array('stat')
);

$ViewList['openapi'] = array(
    'script' => 'openapi.php',
    'params' => array(),
    'unordered_params' => array(),
    'functions' => array('use')
);

$ViewList['openapi.json'] = array(
    'script' => 'openapi.php',
    'params' => array(),
    'unordered_params' => array(),
    'functions' => array('use')
);

$ViewList['export'] = array(
    'script' => 'export.php',
    'params' => array(),
    'functions' => array('use')
);

$ViewList['avatar'] = array(
    'script' => 'avatar.php',
    'params' => array('Id'),
    'functions' => array('use')
);

$ViewList['inbox'] = array(
    'script' => 'inbox.php',
    'params' => array(),
    'unordered_params' => array(),
    'functions' => array('manage')
);


$FunctionList = array();
$FunctionList['use'] = array();
$FunctionList['debug'] = array();
$FunctionList['config'] = array();
$FunctionList['manage'] = array();
$FunctionList['behalf'] = array();
$FunctionList['ws_user'] = array();

$charts = array();
foreach (OpenPaSensorRepository::instance()->getStatisticsService()->getStatisticFactories(true) as $item) {
    $charts[$item->getName()] = array('Name' => $item->getName(), 'value' => $item->getIdentifier());
}
$chartList = array(
    'name' => 'ChartList',
    'values' => $charts
);

$FunctionList['stat'] = array(
    'ChartList' => $chartList,
);
