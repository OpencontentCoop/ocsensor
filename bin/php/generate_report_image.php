<?php

require 'autoload.php';

$script = eZScript::instance(array(
        'description' => ("Generate report images\n\n"),
        'use-session' => false,
        'use-modules' => true,
        'use-extensions' => true)
);

$script->startup();

$options = $script->getOptions(
    '[id:]',
    '',
    array(
        'id' => 'Object id',
    )
);
$script->initialize();
$script->setUseDebugAccumulators(true);

$cli = eZCLI::instance();

$conditions = ['contentclass_id' => eZContentClass::classIDByIdentifier('sensor_report_item')];
/** @var eZContentObject[] $objects */
$objects = eZPersistentObject::fetchObjectList(
    eZContentObject::definition(),
    null,
    $conditions,
    ['published' => 'desc']
);
$objectsCount = count($objects);
$cli->warning($objectsCount);

foreach ($objects as $object){
    $cli->output('#' . $object->attribute('id') . ' ' . $object->attribute('name'));
    $itemDataMap = $object->dataMap();
    if (isset($itemDataMap['data']) && $itemDataMap['data']->hasContent()) {
        if (isset($itemDataMap['images'])) {
            $data = json_decode($itemDataMap['data']->attribute('data_text'), true);
            $images = [];
            $imageUrlList = SensorReport::generateItemImages($data);
            foreach ($imageUrlList as $url) {
                $binary = eZHTTPTool::getDataByURL($url);
                if ($binary) {
                    $filename = basename($url);
                    $dir = sys_get_temp_dir();
                    eZFile::create($filename, $dir, $binary);
                    $images[] = $dir . '/' . $filename;
                    $cli->output( ' -> ' . $url);
                }
            }
            if (!empty($images)) {
                $itemDataMap['images']->fromString(implode('|', $images));
                $itemDataMap['images']->store();
                foreach ($images as $image){
                    @unlink($image);
                }
            }
        }
    }
}

$script->shutdown();
