<?php

require 'autoload.php';

$script = eZScript::instance(array('description' => ("Remove pending user registrations\n\n"),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true));

$script->startup();

$options = $script->getOptions(
    '[time;][dry-run]',
    '',
    array(
        'time' => 'time duration default 86400 60*60*24 seconds (1 day)',
    )
);
$script->initialize();
$script->setUseDebugAccumulators(true);

$cli = eZCLI::instance();

try {

    $timeDuration = isset($options['time']) ? (int)$options['time'] : 86400;
    if (!is_numeric($timeDuration) ||
        $timeDuration < 0) {
        throw new Exception("The time duration must be a positive numeric value (timeDuration = $timeDuration)");
    }

    $contentClassID = eZContentClass::classIDByIdentifier('user');
    $conditions = array(
        'contentclass_id' => $contentClassID,
        'status' => eZContentObject::STATUS_DRAFT
    );
    $idList = [];
    $users = eZContentObject::fetchFilteredList($conditions, null, null, true);
    foreach ($users as $user){
        //$cli->output($user->attribute('name') . ' ' . date('c', $user->attribute('modified')));
        $idList[] = $user->attribute('id');
    }

    $conditions = array(
        'status' => [[eZContentObjectVersion::STATUS_INTERNAL_DRAFT, eZContentObjectVersion::STATUS_DRAFT]],
        'contentobject_id' => array($idList),
    );
    /** @var eZContentObjectVersion[] $versions */
    $versions = eZPersistentObject::fetchObjectList(
        eZContentObjectVersion::definition(),
        null, $conditions, null, null, true
    );
    $expiryTime = time() - $timeDuration; // only remove drafts older than time duration (default is 1 day)
    foreach ($versions as $possibleVersion) {
        $dataMap = $possibleVersion->dataMap();
        $user = eZUser::fetch($possibleVersion->attribute('contentobject_id'));
        $account = $user instanceof eZUser? $user->attribute('email') : '?';
        $message = '#' .
            $possibleVersion->attribute('contentobject_id') . ' ' .
            $possibleVersion->attribute('name') . ' ' .
            date('c', $possibleVersion->attribute('created')) . ' ' .
            $account;

        if ($possibleVersion->attribute('modified') < $expiryTime) {
            $cli->warning($message);
            if (!$options['dry-run']) {
                $possibleVersion->removeThis();
            }
        }else{
            $cli->output($message);
        }
    }

    $script->shutdown();
} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}
