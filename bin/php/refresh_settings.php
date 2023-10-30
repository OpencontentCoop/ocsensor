<?php
require 'autoload.php';

$script = eZScript::instance(array('description' => ("Refresh settings cache\n\n"),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true));

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators(true);
$output = new ezcConsoleOutput();
$cli = eZCLI::instance();

/** @var eZUser $user */
$user = eZUser::fetchByName('admin');
eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));

try{
    eZCache::clearByID(['global_ini']);
    OpenPaSensorRepository::instance()->clearSensorSettingsCache();

}catch (Exception $e){
    $cli->error($e->getMessage());
}

$script->shutdown();