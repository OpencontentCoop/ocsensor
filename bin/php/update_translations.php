<?php

ini_set("memory_limit", "-1");
set_time_limit(0);

require 'autoload.php';

$script = eZScript::instance([
        'description' => ("Update stored informations\n\n"),
        'use-session' => false,
        'use-modules' => true,
        'use-extensions' => true]
);

$script->startup();
$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators(true);
$cli = eZCLI::instance();

SensorTranslationHelper::instance()->resetStaticTranslations();

$script->shutdown();
