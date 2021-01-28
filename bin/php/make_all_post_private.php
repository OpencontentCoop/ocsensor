<?php

use Opencontent\Sensor\Api\Values\Post;

require 'autoload.php';

$script = eZScript::instance(array(
        'description' => ("Make all posts private\n\n"),
        'use-session' => false,
        'use-modules' => true,
        'use-extensions' => true)
);

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators(true);

$user = eZUser::fetchByName('admin');
eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));

try {

    $repository = OpenPaSensorRepository::instance();
    $objects = $repository->getPostContentClass()->objectList();
    foreach ($objects as $object) {
        if ($object->attribute('status') == eZContentObject::STATUS_PUBLISHED) {
            $post = $repository->getPostService()->loadPost($object->attribute('id'));
            if ($post instanceof Post) {
                if ($post->privacy->identifier != 'private') {
                    eZCLI::instance()->warning('#' . $post->id);
                    $repository->getPostService()->setPostStatus($post, 'privacy.private');
                    $repository->getPostService()->refreshPost($post, false);
                }else{
                    eZCLI::instance()->output('#' . $post->id);
                }
            }
        }
    }

    $script->shutdown();
} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}
