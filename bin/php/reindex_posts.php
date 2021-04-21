<?php

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
$cli = eZCLI::instance();

try {

    $repository = OpenPaSensorRepository::instance();
    $cli->output('Fetching objects... ', false);
    $objects = $repository->getPostContentClass()->objectList();
    $objectsCount = count($objects);
    $cli->warning($objectsCount);

    $output = new ezcConsoleOutput();
    $progressBarOptions = array('emptyChar' => ' ', 'barChar' => '=');
    $progressBar = new ezcConsoleProgressbar($output, $objectsCount, $progressBarOptions);
    $progressBar->start();
    foreach ($objects as $object) {
        eZSearch::addObject($object);
        eZContentObject::clearCache();
        $progressBar->advance();
    }
    $progressBar->finish();
    $cli->output();

    $script->shutdown();
} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}
