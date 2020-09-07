<?php
require 'autoload.php';

$script = eZScript::instance(array('description' => ("Remove participant\n\n"),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true));

$script->startup();

$options = $script->getOptions(
    '[id:][user_id:]',
    '',
    array(
        'id' => 'Object id',
        'user_id' => 'User id'
    )
);
$script->initialize();
$script->setUseDebugAccumulators(true);

try {
    if (isset($options['id']) && isset($options['user_id'])) {
        $participantId = $options['user_id'];
        $objectIdList = explode('-', $options['id']);

        foreach ($objectIdList as $objectId) {
            $repository = OpenPaSensorRepository::instance();
            $post = $repository->getPostService()->loadPost($objectId);
            if ($post instanceof \Opencontent\Sensor\Api\Values\Post) {
                $repository->getParticipantService()->removePostParticipant($post, $participantId);
                $repository->getPostService()->refreshPost($post);
            }
        }
    }
    $script->shutdown();
} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}
