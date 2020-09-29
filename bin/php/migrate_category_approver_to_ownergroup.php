<?php

use Opencontent\Sensor\Api\Values\Post;
use Opencontent\Sensor\Legacy\Utils\TreeNode;

require 'autoload.php';

$script = eZScript::instance(array('description' => ("Migrate category approver to owner_group\n\n"),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true));

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators(true);

$user = eZUser::fetchByName( 'admin' );
eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );

try {

    $repository = OpenPaSensorRepository::instance();
    /** @var eZContentObjectTreeNode $child */

    function runOnCategoryChildren(eZContentObjectTreeNode $child){
        eZCLI::instance()->output(' - ' . $child->attribute('name'));
        $dataMap = $child->dataMap();
        if (isset($dataMap['approver']) && isset($dataMap['owner_group']) && $dataMap['approver']->hasContent()){
            $dataMap['owner_group']->fromString($dataMap['approver']->toString());
            $dataMap['owner_group']->store();
            $dataMap['approver']->fromString('');
            $dataMap['approver']->store();

            $child->object()->setAttribute('modified', $child->object()->attribute('modified')+1);
            eZSearch::addObject($child->object());
        }

        foreach ($child->children() as $subChild){
            runOnCategoryChildren($subChild);
        }
    }

    foreach ($repository->getCategoriesRootNode()->children() as $child){
        runOnCategoryChildren($child);
    }

    TreeNode::clearCache($repository->getCategoriesRootNode()->attribute('node_id'));

    $script->shutdown();
} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}
