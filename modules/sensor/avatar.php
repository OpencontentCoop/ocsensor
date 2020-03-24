<?php

$id = (int)$Params['Id'];

$cacheFileHandler = eZClusterFileHandler::instance(eZSys::cacheDirectory() . '/avatars/' . $id . '.png');
$cacheFileHandler->processCache(
    function ($file, $mtime, $identifier) {
    },
    function ($file, $args) {
        $id = $args['id'];
        $object = eZContentObject::fetch((int)$id);
        $name = '?';
        $content = false;
        $style= '';
        if ($object instanceof eZContentObject){
            if ($object->attribute('class_identifier') == 'sensor_group'){
                $style = '&background=8b5d5d&color=f0e9e9';
            }
            $name = $object->attribute('name');
            $dataMap = $object->dataMap();
            if (isset($dataMap['image']) && $dataMap['image']->hasContent()){
                /** @var eZImageAliasHandler $image */
                $image = $dataMap['image']->content();
                $alias = $image->imageAlias('rss');
                if (isset($alias['full_path'])) {
                    $content = eZClusterFileHandler::instance($alias['full_path'])->fetchContents();
                }
            }
        }
        if (!$content){
            $content = file_get_contents('https://eu.ui-avatars.com/api/?name=' . $name . $style);
        }
        return array(
            'binarydata' => $content,
            'scope' => 'avatar-cache',
            'datatype' => 'image/png',
            'store' => true
        );
    },
    -1,
    null,
    array('id' => $id)
);

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