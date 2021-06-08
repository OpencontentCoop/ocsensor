<?php

require 'autoload.php';

$script = eZScript::instance(array(
        'description' => ("Remove id from solr\n\n"),
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

$user = eZUser::fetchByName('admin');
eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));

eZSearch::getEngine()->removeObjectById( (int)$options['id'], true );

$script->shutdown();