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
        $cachePath = eZDir::path(array(eZSys::cacheDirectory(), 'ocopendata', 'avatars', $id . '.png'));
        $cacheFileHandler = eZClusterFileHandler::instance($cachePath);
        $cacheFileHandler->processCache(
            function ($file, $mtime, $identifier) {
            },
            function ($file, $args) {
                $id = $args['id'];
                $content = self::generateAvatar($id);
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

    public static function generateAvatar($id)
    {
        $object = eZContentObject::fetch((int)$id);
        $name = $id == 1 ? 'Op' : 'X';
        $content = false;
        $avatar = new LasseRafn\InitialAvatarGenerator\InitialAvatar();
        if ($object instanceof eZContentObject){
            if ($object->attribute('class_identifier') == 'sensor_group'){
                $avatar->background('#8b5d5d');
                $avatar->color('#f0e9e9');
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
            $avatar->background('#666666');
            $avatar->color('#ffffff');
        }
        $avatar->name($name);
        if (!$content){
            $content = $avatar->generate()->stream('png', 100);
        }
        eZLog::write("Create avatar for user $id", 'sensor.log');

        return $content;
    }
}