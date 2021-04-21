<?php

use Opencontent\Sensor\Api\Values\Message\AuditStruct;

require 'autoload.php';

$script = eZScript::instance(array(
        'description' => ("Merge two user\n\n"),
        'use-session' => false,
        'use-modules' => true,
        'use-extensions' => true)
);

$script->startup();

$options = $script->getOptions(
    '[source:][target:][run]',
    '',
    array(
        'source' => 'Source user id',
        'target' => 'Target user id',
        'run' => 'Run merge'
    )
);
$script->initialize();
$script->setUseDebugAccumulators(true);
$cli = eZCLI::instance();

/** @var eZUser $user */
$user = eZUser::fetchByName('admin');
eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));

try {

    if (!$options['run']) {
        $cli->warning(">>> DRY RUN (add --run argument to make changes) <<< ");
    }

    $repository = OpenPaSensorRepository::instance();
    $source = (int)$options['source'];
    $target = (int)$options['target'];
    $postClassId = (int)$repository->getPostContentClass()->attribute('id');

    $sourceObject = eZContentObject::fetch($source);
    $sourceName = $sourceObject instanceof eZContentObject ? $sourceObject->attribute('name') : 'sconosciuto';
    $apiUser = $repository->getUserService()->loadUser($user->id());

    if ($source === $target || $source == 0 || $target == 0){
        throw new Exception("Invalid source or target");
    }

    if ($source == $user->id()) {
        throw new Exception("Ma che sei pazzo???");
    }

    $db = eZDB::instance();

    $updates = [
        ['table' => 'ezcollab_item', 'field' => 'creator_id'],
        ['table' => 'ezcollab_item_group_link', 'field' => 'user_id'],
        ['table' => 'ezcollab_item_message_link', 'field' => 'participant_id'],
        ['table' => 'ezcollab_item_participant_link', 'field' => 'participant_id'],
        ['table' => 'ezcollab_item_status', 'field' => 'user_id'],
//        ['table' => 'ezcollab_notification_rule', 'field' => 'user_id'],
    ];
    $cli->warning("DB items count");
    foreach ($updates as $update) {
        $table = $update['table'];
        $field = $update['field'];
        $cli->output("$table.$field ", false);
        $query = "SELECT * from $table WHERE $field = $source";
        $rows = $db->arrayQuery($query);
        $cli->warning(count($rows));
    }

    $objectsQuery = "SELECT id from ezcontentobject WHERE owner_id = $source AND contentclass_id = $postClassId";
    $cli->output("ezcontentobject_version ", false);
    $versionQuery = "SELECT id from ezcontentobject_version WHERE creator_id = $source AND contentobject_id in ($objectsQuery)";
    $rows = $db->arrayQuery($versionQuery);
    $cli->warning(count($rows));

    $cli->output("ezcontentobject ", false);
    $rows = $db->arrayQuery($objectsQuery);
    $cli->warning(count($rows));
    $objectsIdList = array_column($rows, 'id');

    $cli->output();
    $cli->warning("DB updates");
    $db->begin();
    foreach ($updates as $update) {
        $table = $update['table'];
        $field = $update['field'];
        $query = "UPDATE $table SET $field = $target WHERE $field = $source";
        $cli->error($query);
        if ($options['run']) {
            $db->query($query);
        }
    }
    $versionUpdateQuery = "UPDATE ezcontentobject_version SET creator_id = $target WHERE creator_id = $source AND contentobject_id in ($objectsQuery)";
    $cli->error($versionUpdateQuery);
    if ($options['run']) {
        $db->query($versionUpdateQuery);
    }
    $objectsUpdateQuery = "UPDATE ezcontentobject SET owner_id = $target WHERE owner_id = $source AND contentclass_id = $postClassId";
    $cli->error($objectsUpdateQuery);
    if ($options['run']) {
        $db->query($objectsUpdateQuery);
    }
    $db->commit();

    $objectCount = count($objectsIdList);
    if ($objectCount > 0) {
        $cli->output();
        $cli->warning("Reindex $objectCount objects");
        $output = new ezcConsoleOutput();
        $progressBarOptions = array('emptyChar' => ' ', 'barChar' => '=');
        $progressBar = new ezcConsoleProgressbar($output, $objectCount, $progressBarOptions);
        $progressBar->start();
        $objects = OpenPABase::fetchObjects($objectsIdList);
        foreach ($objects as $object) {
            if ($options['run']) {

                $post = $repository->getPostService()->loadPost($object->attribute('id'));
                $auditStruct = new AuditStruct();
                $auditStruct->createdDateTime = new \DateTime();
                $auditStruct->creator = $apiUser;
                $auditStruct->post = $post;
                $auditStruct->text = "L'autore della segnalazione è stato modificato. L'autore precedente era $sourceName #$source";
                $repository->getMessageService()->createAudit($auditStruct);

                eZSearch::addObject($object);
                eZContentObject::clearCache();
            }
            $progressBar->advance();
        }
        $progressBar->finish();
    }
    $cli->output();

    $script->shutdown();
} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}
