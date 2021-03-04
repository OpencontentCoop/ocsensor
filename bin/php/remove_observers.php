<?php

use Opencontent\Sensor\Api\Values\Post;

require 'autoload.php';

$script = eZScript::instance(array('description' => ("Remove observers\n\n"),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true));

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

try {
    if (isset($options['id'])) {
        $objectIdList = explode('-', $options['id']);

        foreach ($objectIdList as $objectId) {
            $repository = OpenPaSensorRepository::instance();
            $post = $repository->getPostService()->loadPost($objectId);
            if ($post instanceof Post) {
                foreach ($post->observers->participants as $participant){
                    $repository->getParticipantService()->removePostParticipant($post, $participant->id);
                }
                $repository->getPostService()->refreshPost($post, true);
            }
        }
    }
    $script->shutdown();
} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}
