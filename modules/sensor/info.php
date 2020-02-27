<?php

$Module = $Params['Module'];
$identifier = $Params['Page'];


$ini = eZINI::instance();
$viewCacheEnabled = ($ini->variable('ContentSettings', 'ViewCaching') == 'enabled');

if ($viewCacheEnabled) {
    $expiry = eZExpiryHandler::getTimestamp('template-block-cache', -1);
    $cacheFilePath = SensorModuleFunctions::sensorGlobalCacheFilePath('info-' . $identifier);
    $cacheFile = eZClusterFileHandler::instance($cacheFilePath);
    $Result = $cacheFile->processCache(
        array('SensorModuleFunctions', 'sensorCacheRetrieve'),
        array('SensorModuleFunctions', 'sensorInfoGenerate'),
        null,
        $expiry,
        compact('Params')
    );
} else {
    $data = SensorModuleFunctions::sensorInfoGenerate(false, compact('Params'));
    $Result = $data['content'];
}
return $Result;
