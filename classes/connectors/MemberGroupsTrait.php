<?php

trait MemberGroupsTrait
{
    protected $user;

    protected function removeGroups($groupObjectIdList)
    {
        $object = $this->user->contentObject();
        if ($object instanceof eZContentObject && !empty($groupObjectIdList)) {
            $selectedParentNodeIdArray = [];
            $parentObjectList = OpenPABase::fetchObjects($groupObjectIdList);
            foreach ($parentObjectList as $parentObject){
                $selectedParentNodeIdArray[] = $parentObject->mainNodeID();
            }
            $removeList = [];
            foreach ($object->assignedNodes() as $assignedNode) {
                if (in_array($assignedNode->attribute('parent_node_id'), $selectedParentNodeIdArray)) {
                    $removeList[] = $assignedNode->attribute('node_id');
                }
            }
            if (!empty($removeList)) {
                eZContentOperationCollection::removeNodes($removeList);
            }
        }
    }

    /**
     * @see eZContentOperationCollection::addAssignment
     */
    protected function addGroups($groupObjectIdList)
    {
        $object = $this->user->contentObject();
        if ($object instanceof eZContentObject && !empty($groupObjectIdList)) {
            $selectedParentNodeIdArray = [];
            $parentObjectList = OpenPABase::fetchObjects($groupObjectIdList);
            foreach ($parentObjectList as $parentObject){
                $selectedParentNodeIdArray[] = $parentObject->mainNodeID();
            }
            /** @var eZContentObjectTreeNode $node */
            $node = $object->mainNode();
            if (!$node instanceof eZContentObjectTreeNode){
                return;
            }
            $nodeAssignmentList = eZNodeAssignment::fetchForObject($object->attribute('id'), $object->attribute('current_version'), 0, false);
            $assignedNodes = $object->assignedNodes();
            $parentNodeIdArray = array();
            foreach ($assignedNodes as $assignedNode) {
                $append = false;
                foreach ($nodeAssignmentList as $nodeAssignment) {
                    if ($nodeAssignment['parent_node'] == $assignedNode->attribute('parent_node_id')) {
                        $append = true;
                        break;
                    }
                }
                if ($append) {
                    $parentNodeIdArray[] = $assignedNode->attribute('parent_node_id');
                }
            }
            $db = eZDB::instance();
            $db->begin();
            $locationAdded = false;
            foreach ($selectedParentNodeIdArray as $selectedParentNodeID) {
                if (!in_array($selectedParentNodeID, $parentNodeIdArray)) {
                    $insertedNode = $object->addLocation($selectedParentNodeID, true);
                    $insertedNode->setAttribute('contentobject_is_published', 1);
                    $insertedNode->setAttribute('main_node_id', $node->attribute('main_node_id'));
                    $insertedNode->setAttribute('contentobject_version', $node->attribute('contentobject_version'));
                    $insertedNode->updateSubTreePath();
                    $insertedNode->sync();
                    $locationAdded = true;
                }
            }
            if ($locationAdded) {
                eZSearch::addNodeAssignment($object->attribute('main_node_id'), $object->attribute('id'), $selectedParentNodeIdArray);
                eZUser::purgeUserCacheByUserId($object->attribute('id'));
                eZContentCacheManager::clearContentCacheIfNeeded($object->attribute('id'));
            }
            $db->commit();
        }
    }
}