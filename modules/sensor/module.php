<?php
$Module = ['name' => 'Sensor'];

$ViewList = [];
$ViewList['home'] = [
    'script' => 'home.php',
    'functions' => ['use'],
];

$ViewList['info'] = [
    'script' => 'info.php',
    'params' => ['Page'],
    'functions' => ['use'],
];

$ViewList['posts'] = [
    'script' => 'posts.php',
    'params' => ['ID', 'Offset'],
    'functions' => ['use'],
];

$ViewList['add'] = [
    'script' => 'add.php',
    'params' => [],
    'functions' => ['use'],
];

$ViewList['copy'] = [
    'script' => 'copy.php',
    'params' => ['Id'],
    'functions' => ['use'],
];

$ViewList['edit'] = [
    'script' => 'edit.php',
    'params' => ['ID'],
    'functions' => ['use'],
];

$ViewList['dashboard'] = [
    'script' => 'dashboard.php',
    'params' => ["Part", "Group", "Export"],
    'unordered_params' => [
        "list" => "List",
        "offset" => "Offset",
    ],
    'functions' => ['use'],
];

$ViewList['redirect'] = [
    'script' => 'redirect.php',
    'params' => ['View'],
    'functions' => ['use'],
];

$ViewList['test_mail'] = [
    'script' => 'test_mail.php',
    'params' => ['Type', 'Id', 'Param', 'Param2'],
    'functions' => ['debug'],
];

$ViewList['config'] = [
    'script' => 'config.php',
    'params' => ["Part"],
    'unordered_params' => ['offset' => 'Offset'],
    'functions' => ['config'],
];

$ViewList['notifications'] = [
    'script' => 'notifications.php',
    'params' => ['UserId', 'Type', 'SubType'],
    'functions' => ['config'],
];

$ViewList['stat'] = [
    'script' => 'stat.php',
    'params' => ['ChartIdentifier'],
    'unordered_params' => [],
    'functions' => ['stat'],
];

$ViewList['openapi'] = [
    'script' => 'openapi.php',
    'params' => [],
    'unordered_params' => [],
    'functions' => ['use'],
];

$ViewList['openapi.json'] = [
    'script' => 'openapi.php',
    'params' => [],
    'unordered_params' => [],
    'functions' => ['use'],
];

$ViewList['export'] = [
    'script' => 'export.php',
    'params' => [],
    'functions' => ['use'],
];

$ViewList['avatar'] = [
    'script' => 'avatar.php',
    'params' => ['Id'],
    'functions' => ['use'],
];

$ViewList['inbox'] = [
    'script' => 'inbox.php',
    'params' => [],
    'unordered_params' => [],
    'functions' => ['manage'],
];

$ViewList['user'] = [
    'script' => 'user.php',
    'params' => ['ID'],
    'unordered_params' => [],
    'functions' => ['user_list'],
];

$ViewList['stat_source'] = [
    'script' => 'stat_source.php',
    'params' => ['Repository'],
    'functions' => ['debug'],
];

$ViewList['report'] = [
    'script' => 'report.php',
    'params' => ['RemoteId', 'Action', 'SlideId'],
    'unordered_params' => [],
    'functions' => ['report'],
];
$ViewList['alert'] = [
    'script' => 'alert.php',
    'ui_context' => 'administration',
    'params' => [],
    'functions' => ['use'],
];

$ViewList['criticals'] = [
    'script' => 'criticals.php',
    'params' => ['api'],
    'functions' => ['criticals'],
];

$ViewList['metrics'] = [
    'script' => 'metrics.php',
    'functions' => ['metrics'],
];


$FunctionList = [];
$FunctionList['use'] = [];
$FunctionList['debug'] = [];
$FunctionList['config'] = [];
$FunctionList['manage'] = [];
$FunctionList['behalf'] = [];
$FunctionList['ws_user'] = [];
$FunctionList['user_list'] = [];
$FunctionList['report'] = [];
$FunctionList['metrics'] = [];
$FunctionList['criticals'] = [];

$charts = [];
foreach (OpenPaSensorRepository::instance()->getStatisticsService()->getStatisticFactories(true) as $item) {
    $charts[$item->getName()] = ['Name' => $item->getName(), 'value' => $item->getIdentifier()];
}
$chartList = [
    'name' => 'ChartList',
    'values' => $charts,
];

$FunctionList['stat'] = [
    'ChartList' => $chartList,
];

$FunctionList['category_access'] = [
    'Node' => [
        'name' => 'Node',
        'values' => [],
    ],
];
