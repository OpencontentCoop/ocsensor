<?php

$module = $Params['Module'];

$tpl = eZTemplate::factory();
$tpl->setVariable('sensor_home', true);

$currentUser = eZUser::currentUser();

$ini = eZINI::instance();
$viewCacheEnabled = ($ini->variable('ContentSettings', 'ViewCaching') == 'enabled');

if ($viewCacheEnabled) {
    $expiry = eZExpiryHandler::getTimestamp('template-block-cache', -1);
    $cacheFilePath = SensorModuleFunctions::sensorGlobalCacheFilePath($currentUser->isAnonymous() ? 'home-anon' : 'home');
    $cacheFile = eZClusterFileHandler::instance($cacheFilePath);
    $Result = $cacheFile->processCache(
        array('SensorModuleFunctions', 'sensorCacheRetrieve'),
        array('SensorModuleFunctions', 'sensorHomeGenerate'),
        null,
        $expiry,
        compact('Params')
    );
} else {
    $data = SensorModuleFunctions::sensorHomeGenerate(false, compact('Params'));
    $Result = $data['content'];
}
return $Result;
