<?php

class SensorAvatar
{
    public static function clearCache()
    {
        $commonPath = eZDir::path(array(eZSys::cacheDirectory()));
        $fileHandler = eZClusterFileHandler::instance();
        $commonSuffix = '';
        $fileHandler->fileDeleteByDirList(array('avatars'), $commonPath, $commonSuffix);
    }

    /**
     * @param $id
     * @return eZClusterFileHandlerInterface
     */
    public static function getAvatar($id)
    {
        $cacheFileHandler = eZClusterFileHandler::instance(eZSys::cacheDirectory() . '/avatars/' . $id . '.png');
        $cacheFileHandler->processCache(
            function ($file, $mtime, $identifier) {
            },
            function ($file, $args) {
                $id = $args['id'];
                $object = eZContentObject::fetch((int)$id);
                $name = $id == 1 ? 'Op' : 'X';
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
                }else{
                    $style = '&background=666666&color=ffffff';
                }
                if (!$content){
                    $content = file_get_contents('https://eu.ui-avatars.com/api/?name=' . $name . $style);
                }
                eZLog::write("Create avatar for user $id", 'sensor.log');
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

        return $cacheFileHandler;
    }
}