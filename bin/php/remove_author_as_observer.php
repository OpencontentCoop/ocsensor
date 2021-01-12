<?php

use Opencontent\Sensor\Api\Values\Participant;
use Opencontent\Sensor\Api\Values\ParticipantRole;
use Opencontent\Sensor\Api\Values\Post;

require 'autoload.php';

$script = eZScript::instance(array('description' => ("Remove participant\n\n"),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true));

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators(true);

$user = eZUser::fetchByName( 'admin' );
eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );

$cli = eZCLI::instance();

try {

    $repository = OpenPaSensorRepository::instance();

    /** @var eZContentObjectTreeNode[] $nodes */
    $nodes = eZContentObjectTreeNode::subTreeByNodeID([
        'ClassFilterType' => 'include',
        'ClassFilterArray' => [$repository->getPostContentClassIdentifier()],
        'SortBy' => ['published', false]
    ], 1);

    $roles = $repository->getParticipantService()->loadParticipantRoleCollection();
    $roleAuthor = $roles->getParticipantRoleById(ParticipantRole::ROLE_AUTHOR);

    foreach ($nodes as $node){
        $post = $repository->getPostService()->loadPost($node->attribute('contentobject_id'));
        $cli->output($post->id);
        foreach ($post->observers->participants as $participant){
            if ($participant->type == Participant::TYPE_USER){
                foreach ($participant->users as $user){
                    if ($user->type !== 'sensor_operator'){
                        $cli->warning("Remove $participant->name from $post->id");
                        $repository->getParticipantService()->addPostParticipant($post, $participant->id, $roleAuthor);
                        $repository->getPostService()->refreshPost($post);
                    }
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
