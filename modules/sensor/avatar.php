<?php

$id = (int)$Params['Id'];

$cacheFileHandler = SensorAvatar::getAvatar($id);

$filesize = $cacheFileHandler->size();
$mtime = $cacheFileHandler->mtime();
$datatype = $cacheFileHandler->dataType();

header("Content-Type: {$datatype}");
header("Connection: close");
header('Served-by: ' . $_SERVER["SERVER_NAME"]);
header("Last-Modified: " . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
header("ETag: $mtime-$filesize");
header("Cache-Control: max-age=2592000 s-max-age=2592000");

$cacheFileHandler->passthrough();
eZExecution::cleanExit();