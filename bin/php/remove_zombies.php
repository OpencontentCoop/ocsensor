<?php

require 'autoload.php';

$script = eZScript::instance(array('description' => ("Remove zombie contents\n\n"),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true));

$script->startup();

$options = $script->getOptions(
    '[fix-empty-objects][fix-empty-nodes][remove-draft][time;][dry-run]',
    '',
    array(
        'time' => 'time duration default 86400 60*60*24 seconds (1 day)',
    )
);
$script->initialize();
$script->setUseDebugAccumulators(true);

$cli = eZCLI::instance();

$db = eZDB::instance();
$output = new ezcConsoleOutput();
$output->formats->success->color = 'green';
$output->formats->failure->color = 'red';
$outputOptions = array(
    'successChar' => $output->formatText( '+', 'success' ),
    'failureChar' => $output->formatText( '-', 'failure' ),
);

function checkObjectsWithoutAttributes($doFix = false)
{
    global $db, $output, $outputOptions;

    $objects = $db->arrayQuery(
        "SELECT id, contentclass_id, current_version FROM ezcontentobject"
    );

    if ($output) {
        $status = new ezcConsoleStatusbar($output, $outputOptions);
    }

    $i = 0;
    foreach ($objects as $item) {
        $hasAttribute = $db->arrayQuery(
            "SELECT contentobject_id FROM ezcontentobject_attribute WHERE contentobject_id = " . $item['id']
        );

        if (empty( $hasAttribute )) {

            if ($doFix) {
                $db->begin();
                $db->query("DELETE FROM ezcontentobject WHERE ezcontentobject.id = " . $item['id']);
                $db->query(
                    "DELETE FROM ezcontentobject_name WHERE ezcontentobject_name.contentobject_id = " . $item['id']
                );
                if ($item['contentclass_id'] == 4) {
                    $db->query("DELETE FROM ezuser WHERE ezuser.contentobject_id = " . $item['id']);
                }
                $db->commit();
            } else {
                if (isset($status)) $status->add(false);
            }
            $i++;
        }
        if (isset($status)) $status->add(true);
    }

    if ($output){
        $output->outputLine();
        $output->outputLine( 'Successes: ' . $status->getSuccessCount() . ', Failures: ' . $status->getFailureCount() );
    }

    return $i;
}

function checkNodesConsistency($doFix = false)
{
    global $db, $output, $outputOptions;

    $objects = $db->arrayQuery("SELECT id, name, status FROM ezcontentobject WHERE status = " . eZContentObject::STATUS_PUBLISHED);

    $count = 0;

    if ($output) {
        $status = new ezcConsoleStatusbar($output, $outputOptions);
    }

    foreach ($objects as $item) {

        $hasNode = $db->arrayQuery(
            "SELECT contentobject_id FROM ezcontentobject_tree WHERE contentobject_id = " . $item['id']
        );

        $hasTrashNode = $db->arrayQuery(
            "SELECT contentobject_id FROM ezcontentobject_trash WHERE contentobject_id = " . $item['id']
        );

        if (empty( $hasNode ) && empty( $hasTrashNode )) {
            if (isset($status)) $status->add(false);
            $count++;
        } else {
            if (isset($status)) $status->add(true);
        }
    }

    if ($doFix) {

        foreach ($objects as $item) {
            $hasNode = $db->arrayQuery(
                "SELECT contentobject_id FROM ezcontentobject_tree WHERE contentobject_id = " . $item['id']
            );
            $hasTrashNode = $db->arrayQuery(
                "SELECT contentobject_id FROM ezcontentobject_trash WHERE contentobject_id = " . $item['id']
            );

            if (empty( $hasNode ) && empty( $hasTrashNode )) {

                $db->begin();
                $db->query(
                    "DELETE FROM ezcontentobject WHERE id = " . $item['id']
                );

                $db->query(
                    "DELETE FROM ezcontentobject_name WHERE contentobject_id = " . $item['id']
                );

                $db->query(
                    "DELETE FROM ezcontentobject_link WHERE (from_contentobject_id = " . $item['id'] . " OR to_contentobject_id = " . $item['id'] . ")"
                );

                $db->query(
                    "DELETE FROM ezcontentobject_version WHERE contentobject_id = " . $item['id']
                );

                $db->query(
                    "DELETE FROM ezcontentobject_attribute WHERE contentobject_id = " . $item['id']
                );

                $db->query(
                    "DELETE FROM eznode_assignment WHERE contentobject_id = " . $item['id']
                );
                $db->commit();
            }
        }
    }
    if ($output){
        $output->outputLine();
        $output->outputLine( 'Successes: ' . $status->getSuccessCount() . ', Failures: ' . $status->getFailureCount() );
    }

    return $count;
}

try {

    if ($options['fix-empty-objects']) {
        checkObjectsWithoutAttributes(!$options['dry-run']);
    }
    if ($options['fix-empty-nodes']) {
        checkNodesConsistency(!$options['dry-run']);
    }

    if ($options['remove-draft']) {
        $timeDuration = isset($options['time']) ? (int)$options['time'] : 86400;
        if (!is_numeric($timeDuration) ||
            $timeDuration < 0) {
            throw new Exception("The time duration must be a positive numeric value (timeDuration = $timeDuration)");
        }

        $conditions = array(
            'status' => eZContentObject::STATUS_DRAFT
        );
        $idList = [];
        $objects = eZContentObject::fetchFilteredList($conditions, null, null, true);
        foreach ($objects as $object) {
            //$cli->output($object->attribute('name') . ' ' . date('c', $object->attribute('modified')));
            $idList[$object->attribute('id')] = $object->attribute('id');
        }

        $conditions = array(
            'status' => [[eZContentObjectVersion::STATUS_INTERNAL_DRAFT, eZContentObjectVersion::STATUS_DRAFT]],
            'contentobject_id' => array(array_values($idList)),
        );
        /** @var eZContentObjectVersion[] $versions */
        $versions = eZPersistentObject::fetchObjectList(
            eZContentObjectVersion::definition(),
            null, $conditions, ['contentobject_id' => 'asc'], null, true
        );
        $expiryTime = time() - $timeDuration; // only remove drafts older than time duration (default is 1 day)
        foreach ($versions as $possibleVersion) {
            unset($idList[$possibleVersion->attribute('contentobject_id')]);
            $dataMap = $possibleVersion->dataMap();
            $message = '#' .
                $possibleVersion->attribute('contentobject_id') . ' ' .
                $possibleVersion->attribute('name') . ' ' .
                date('c', $possibleVersion->attribute('created'));

            if ($possibleVersion->attribute('modified') < $expiryTime) {
                $cli->warning($message);
                if (!$options['dry-run']) {
                    $possibleVersion->removeThis();
                }
            } else {
                $cli->output($message);
            }
        }
    }

    $script->shutdown();
} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}
