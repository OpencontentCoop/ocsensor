<?php

use Opencontent\Sensor\Legacy\Scenarios\SensorScenario;
use Opencontent\Sensor\Legacy\Utils\TreeNode;

require 'autoload.php';

$script = eZScript::instance(array(
        'description' => ("Migrate category configurations to scenarios\n\n"),
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

try {

    $sensorRemoteId = OpenPABase::getCurrentSiteaccessIdentifier() . '_openpa_sensor';
    $scenarioRoot = eZContentObject::fetchByRemoteID($sensorRemoteId . '_scenarios');
    if (!$scenarioRoot instanceof eZContentObject){
        eZContentFunctions::createAndPublishObject([
            'parent_node_id' => eZContentObject::fetchByRemoteID($sensorRemoteId)->attribute('main_node_id'),
            'class_identifier' => 'folder',
            'remote_id' => $sensorRemoteId . '_scenarios',
            'attributes' => ['name' => 'Scenari']
        ]);
    }

    $repository = OpenPaSensorRepository::instance();

    function runOnCategoryChildren(eZContentObjectTreeNode $child, $level = 0)
    {
        global $repository;
        $space = str_pad(' ', $level*2);
        eZCLI::instance()->output($space . '- ' . $child->attribute('name') . '... ', false);


        $dataMap = $child->dataMap();

        if (
            $dataMap['approver']->hasContent()
            || $dataMap['owner_group']->hasContent()
            || $dataMap['owner']->hasContent()
            || $dataMap['observer']->hasContent()
        ) {

            $attributes = [
                'approver' => $dataMap['approver']->toString(),
                'owner_group' => $dataMap['owner_group']->toString(),
                'owner' => $dataMap['owner']->toString(),
                'observer' => $dataMap['observer']->toString(),
                'triggers' => 'on_add_category',
                'criterion_type' => '',
                'criterion_category' => $child->attribute('contentobject_id'),
                'criterion_area' => '',
                'criterion_reporter_group' => '',
                'random_owner' => empty($dataMap['owner']->toString()) && $repository->getSensorSettings()->get('CategoryAutomaticAssignToRandomOperator') ? '1' : '0'
            ];

            $remoteId = SensorScenario::generateRemoteId($attributes);
            $exists = eZContentObject::fetchByRemoteID($remoteId);
            if ($exists instanceof eZContentObject) {
                eZCLI::instance()->warning('Already exixts');
            } else {
                eZContentFunctions::createAndPublishObject([
                    'parent_node_id' => $repository->getScenariosRootNode()->attribute('node_id'),
                    'class_identifier' => 'sensor_scenario',
                    'remote_id' => $remoteId,
                    'attributes' => $attributes
                ]);
                eZCLI::instance()->warning('OK');
            }
        } else {
            eZCLI::instance()->error('No data');
        }

        foreach ($child->children() as $subChild) {
            $level++;
            runOnCategoryChildren($subChild, $level);
            $level--;
        }
    }

    function runOnAreaChildren(eZContentObjectTreeNode $child)
    {
        global $repository;
        eZCLI::instance()->output(' - ' . $child->attribute('name') . '... ', false);

        $dataMap = $child->dataMap();

        if ($dataMap['observer']->hasContent()) {
            $attributes = [
                'approver' => '',
                'owner_group' => '',
                'owner' => '',
                'observer' => $dataMap['observer']->toString(),
                'triggers' => 'on_create',
                'criterion_type' => '',
                'criterion_category' => '',
                'criterion_area' => $child->attribute('contentobject_id'),
                'criterion_reporter_group' => '',
                'random_owner' => '0'
            ];
            $remoteId = SensorScenario::generateRemoteId($attributes);
            $exists = eZContentObject::fetchByRemoteID($remoteId);
            if ($exists instanceof eZContentObject) {
                eZCLI::instance()->warning('Already exixts');
            } else {
                eZContentFunctions::createAndPublishObject([
                    'parent_node_id' => $repository->getScenariosRootNode()->attribute('node_id'),
                    'class_identifier' => 'sensor_scenario',
                    'remote_id' => $remoteId,
                    'attributes' => $attributes
                ]);
                eZCLI::instance()->warning('OK');
            }
        } else {
            eZCLI::instance()->error('No data');
        }


        foreach ($child->subTree([
            'ClassFilterType' => 'include',
            'ClassFilterArray' => ['sensor_area'],
            'Depth' => 1,
            'DepthOperator' => 'eq'
        ]) as $subChild) {
            runOnAreaChildren($subChild);
        }
    }

    /** @var eZContentObjectTreeNode $child */
    foreach ($repository->getCategoriesRootNode()->children() as $child) {
        runOnCategoryChildren($child);
    }

    foreach ($repository->getAreasRootNode()->subTree([
        'ClassFilterType' => 'include',
        'ClassFilterArray' => ['sensor_area'],
        'Depth' => 1,
        'DepthOperator' => 'eq'
    ]) as $child) {
        runOnAreaChildren($child);
    }

    TreeNode::clearCache($repository->getCategoriesRootNode()->attribute('node_id'));
    TreeNode::clearCache($repository->getAreasRootNode()->attribute('node_id'));

    $script->shutdown();
} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}
