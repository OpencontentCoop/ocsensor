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

$ViewList['public_posts'] = array(
    'script' => 'public_posts.php',
    'params' => array('ID', 'Offset'),
    'functions' => array('use')
);

$ViewList['data'] = array(
    'script' => 'data.php',
    'params' => array(),
    'functions' => array('use')
);

$ViewList['add'] = array(
    'script' => 'add.php',
    'params' => array(),
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
    'params' => array('UserId','Type','SubType'),
    'functions' => array('config')
);

$ViewList['stat'] = array(
    'script' => 'stat.php',
    'params' => array('ChartIdentifier'),
    'unordered_params' => array(),
    'functions' => array('stat')
);

//bc
$ViewList['dimmi'] = array(
    'script' => 'dimmi.php',
    'params' => array(),
    'functions' => array('use')
);


$FunctionList = array();
$FunctionList['use'] = array();
$FunctionList['debug'] = array();
$FunctionList['config'] = array();
$FunctionList['manage'] = array();
$FunctionList['behalf'] = array();
$FunctionList['ws_user'] = array();

$charts = array();
foreach( SensorCharts::listAvailableCharts() as $item )
{
    $charts[$item['name']] = array( 'Name' => $item['name'], 'value' => $item['identifier'] );
}
$chartList = array(
    'name' => 'ChartList',
    'values' => $charts
);

$FunctionList['stat'] = array(
    'ChartList' => $chartList,
);
