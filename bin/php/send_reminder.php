<?php
require 'autoload.php';

$script = eZScript::instance(array('description' => ("Send sensor newsletter\n\n"),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true));

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators(true);
$output = new ezcConsoleOutput();
$cli = eZCLI::instance();

try{

    OpenPaSensorRepository::instance()->getEventService()->getEmitter()->emit('reminder');

}catch (Exception $e){
    $cli->error($e->getMessage());
}

$script->shutdown();