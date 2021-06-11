<?php

require 'autoload.php';

$script = eZScript::instance(array(
        'description' => ("Make all posts private\n\n"),
        'use-session' => false,
        'use-modules' => true,
        'use-extensions' => true)
);

$script->startup();

$options = $script->getOptions(
    '[user:]',
    '',
    array(
        'user' => 'Filter by user id',
    )
);
$script->initialize();
$script->setUseDebugAccumulators(true);

$user = eZUser::fetchByName('admin');
eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));
$cli = eZCLI::instance();

try {

    $repository = OpenPaSensorRepository::instance();
    $cli->output('Fetching objects... ', false);
    $conditions = ['contentclass_id' => $repository->getPostContentClass()->attribute('id')];
    if ($options['user']){
        $conditions['owner_id'] = (int)$options['user'];
    }
    $objects = eZPersistentObject::fetchObjectList(
        eZContentObject::definition(),
        null,
        $conditions,
        ['published' => 'desc']
    );
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

    $customRepository = new SensorDailyReportRepository();
    $customRepository->truncate();
    $processLength = $customRepository->countSearchableObjects();
    $cli->warning('Reindex ' . $processLength . '  object of repository ' . $customRepository->getIdentifier());
    if ($processLength > 0) {
        $percentageAdvancementStep = 100 / $processLength;
    } else {
        $percentageAdvancementStep = 0;
    }

    $progressBarOptions = array(
        'emptyChar' => ' ',
        'barChar' => '='
    );
    $progressBar = new ezcConsoleProgressbar($output, $processLength, $progressBarOptions);
    $progressBar->start();
    $length = 50;
    $offset = 0;
    do {
        $items = $customRepository->fetchSearchableObjectList($length, $offset);
        foreach ($items as $item) {
            $progressBar->advance();
            if (!$customRepository->index($item)){
                $errors[$customRepository->getIdentifier()][] = $item;
            }
        }

        $offset += $length;
    } while (count($items) == $length);

    $progressBar->finish();
    $cli->notice();

    $script->shutdown();
} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}
