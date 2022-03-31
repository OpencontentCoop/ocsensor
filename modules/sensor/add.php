<?php
/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();

$module->redirectTo('/#add');
return;
