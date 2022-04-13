<?php

use Opencontent\Sensor\Api\Values\Message\TimelineItem;
use Opencontent\Sensor\Api\Values\Post;
use Opencontent\Sensor\Legacy\Utils\TreeNode;
use Opencontent\Sensor\Legacy\Utils\TreeNodeItem;

class SensorTimelinePersistentObject extends eZPersistentObject
{
    const DATE_FORMAT = 'Y-m-d H:i:s';

    private static $isSchemaInstalled;

    private static function isSchemaInstalled()
    {
        if (self::$isSchemaInstalled === null){
            $dbName = eZINI::instance()->variable('DatabaseSettings', 'Database');
            $tableName = 'ocsensor_timeline';
            $res = eZDB::instance()->arrayQuery("SELECT EXISTS (SELECT FROM information_schema.tables WHERE  table_catalog = '$dbName' AND table_name   = '$tableName');");
            self::$isSchemaInstalled = $res[0]['exists'] == 't';
        }

        return self::$isSchemaInstalled;
    }

    public static function definition()
    {
        return [
            'fields' => [
                'timeline_id' => [
                    'name' => 'timeline_id',
                    'datatype' => 'integer',
                ],
                'post_id' => [
                    'name' => 'post_id',
                    'datatype' => 'integer',
                    'default' => null,
                ],
                'executor_id' => [
                    'name' => 'executor_id',
                    'datatype' => 'integer',
                    'default' => null,
                ],
                'group_id_in_charge' => [
                    'name' => 'group_id_in_charge',
                    'datatype' => 'integer',
                    'default' => null,
                ],
                'target_operator_id' => [
                    'name' => 'target_operator_id',
                    'datatype' => 'integer',
                    'default' => null,
                ],
                'target_group_id' => [
                    'name' => 'target_group_id',
                    'datatype' => 'integer',
                    'default' => null,
                ],
                'action' => [
                    'name' => 'action',
                    'datatype' => 'string',
                    'default' => null,
                ],
                'status_at_end' => [
                    'name' => 'status_at_end',
                    'datatype' => 'string',
                    'default' => null,
                ],
                'start_at' => [
                    'name' => 'start_at',
                    'datatype' => 'string',
                    'default' => null,
                ],
                'end_at' => [
                    'name' => 'end_at',
                    'datatype' => 'string',
                    'default' => null,
                ],
                'duration' => [
                    'name' => 'duration',
                    'datatype' => 'integer',
                    'default' => null,
                ],
                'post_parent_category_id' => [
                    'name' => 'post_parent_category_id',
                    'datatype' => 'integer',
                    'default' => null,
                ],
                'post_child_category_id' => [
                    'name' => 'post_child_category_id',
                    'datatype' => 'integer',
                    'default' => null,
                ],
                'post_area_id' => [
                    'name' => 'post_area_id',
                    'datatype' => 'integer',
                    'default' => null,
                ],
                'post_author_id' => [
                    'name' => 'post_author_id',
                    'datatype' => 'integer',
                    'default' => null,
                ],
                'post_author_group_id' => [
                    'name' => 'post_author_group_id',
                    'datatype' => 'integer',
                    'default' => null,
                ],
                'post_status' => [
                    'name' => 'post_status',
                    'datatype' => 'string',
                    'default' => null,
                ],
                'post_type' => [
                    'name' => 'post_type',
                    'datatype' => 'string',
                    'default' => null,
                ],
            ],
            'keys' => ['timeline_id', 'post_id'],
            'class_name' => 'SensorTimelinePersistentObject',
            'name' => 'ocsensor_timeline',
        ];
    }

    public static function createOnPublishNewPost(Post $post)
    {
        if (!self::isSchemaInstalled()){
            return false;
        }

        $item = [
            'timeline_id' => 0,
            'post_id' => $post->id,
            'executor_id' => $post->reporter ? $post->reporter->id : $post->author->id,
            'action' => 'creating',
            'status_at_end' => 'created',
            'start_at' => $post->published->format(self::DATE_FORMAT),
            'end_at' => $post->published->format(self::DATE_FORMAT),
            'duration' => 0,
            'post_area_id' => count($post->areas) > 0 ? $post->areas[0]->id : null,
            'post_author_id' => $post->author->id,
            'post_author_group_id' => $post->author->type == 'user' && count(
                $post->author->groups
            ) > 0 ? $post->author->groups[0] : null,
            'post_status' => 'pending',
            'post_type' => $post->type->identifier,
        ];
        $row = new self($item);
        $row->store();

        return $row;
    }

    public static function createOnNewTimelineItem(Post $post, TimelineItem $timelineItem)
    {
        if (!self::isSchemaInstalled()){
            return false;
        }

        $targetOperator = $targetGroup = $latestTargetOperator = $latestTargetGroup = null;
        $extra = $timelineItem->extra;
        if (!empty($extra) && $timelineItem->type == 'assigned') {
            $operatorClassId = eZContentClass::classIDByIdentifier('sensor_operator');
            $groupClassId = eZContentClass::classIDByIdentifier('sensor_group');
            $objects = eZPersistentObject::fetchObjectList(
                eZContentObject::definition(),
                ['id', 'contentclass_id'],
                ['contentclass_id' => [[$groupClassId, $operatorClassId,]], 'id' => [$extra]],
                null,
                null,
                false
            );
            foreach ($objects as $object) {
                if ($object['contentclass_id'] == $operatorClassId) {
                    $targetOperator = (int)$object['id'];
                }
                if ($object['contentclass_id'] == $groupClassId) {
                    $targetGroup = (int)$object['id'];
                }
            }
        }

        $previous = $previousWithTarget = $previousRead = $first = false;
        /** @var SensorTimelinePersistentObject[] $previousList */
        $previousList = SensorTimelinePersistentObject::fetchObjectList(
            SensorTimelinePersistentObject::definition(),
            null,
            ['post_id' => $post->id, 'timeline_id' => ['<', $timelineItem->id]],
            ['timeline_id' => 'desc']
        );
        if (count($previousList) > 0) {
            foreach ($previousList as $item) {
                if ($item->attribute('target_group_id') && !$previousWithTarget) {
                    $previousWithTarget = $item;
                }
                if ($item->attribute('status_at_end') == 'read' && !$previousRead) {
                    $previousRead = $item;
                }
            }
            $previous = array_shift($previousList);
            $first = array_pop($previousList);
        }

        if ($previousWithTarget instanceof SensorTimelinePersistentObject) {
            $latestTargetOperator = (int)$previousWithTarget->attribute('target_operator_id');
            $latestTargetGroup = (int)$previousWithTarget->attribute('target_group_id');
        }

        $executorId = $timelineItem->creator->id;
        $groupIdInCharge = $latestTargetGroup;
        if ($previous instanceof SensorTimelinePersistentObject
            && ($previous->attribute('status_at_end') === 'fixed'
                || $previous->attribute('status_at_end') === 'closed'
                || $previous->attribute('status_at_end') === 'reopened')
            && $previousRead instanceof SensorTimelinePersistentObject) {
            $groupIdInCharge = $previousRead->attribute('group_id_in_charge');
        }
        if (!$groupIdInCharge){
            $groupIdInCharge = count($timelineItem->creator->groups) > 0 ? $timelineItem->creator->groups[0] : null;
        }

        $startAt = $status = $action = false;

        if ($previous instanceof SensorTimelinePersistentObject) {
            $startAt = DateTime::createFromFormat(self::DATE_FORMAT, $previous->attribute('end_at'));
        }

        switch ($timelineItem->type) {
            case 'read':
                if (count($previousList) >= 1) {
                    $startAt = $post->published;
                }
                $status = 'open';
                $action = $previous instanceof SensorTimelinePersistentObject && $previous->attribute('status_at_end') == 'reopened' ? 'reassigning' : 'reading';
                break;

            case 'assigned':
            case 'fixed':
                if ($timelineItem->type == 'assigned') {
                    $action = 'reassigning';
                } else {
                    $action = 'fixing';
                }
                if ($previous instanceof SensorTimelinePersistentObject) {
                    $status = 'open';
                    if ($previous->attribute('status_at_end') == 'read' && $timelineItem->type == 'assigned') {
                        $action = $first === $previous ? 'reassigning' :'assigning';
                    }
                    if ($previous->attribute('status_at_end') == 'assigned'
                        && $timelineItem->type == 'assigned'
                        && $targetGroup == $groupIdInCharge) {
                        $action = 'internal_reassigning';
                    }
                }
                break;

            case 'closed':
                $action = 'closing';
                $status = 'close';
                break;

            case 'reopened':
                $action = 'reopening';
                $status = 'pending';
                break;
        }

        $parentAndChildCategory = self::getParentAndChildCategory(count($post->categories) > 0 ? $post->categories[0]->id : null);

        $item = [
            'timeline_id' => $timelineItem->id,
            'post_id' => $post->id,
            'executor_id' => $executorId,
            'group_id_in_charge' => $groupIdInCharge,
            'target_operator_id' => $targetOperator,
            'target_group_id' => $targetGroup,
            'action' => $action,
            'status_at_end' => $timelineItem->type,
            'end_at' => $timelineItem->published->format(self::DATE_FORMAT),
            'post_parent_category_id' => $parentAndChildCategory['parent'],
            'post_child_category_id' => $parentAndChildCategory['child'],
            'post_area_id' => count($post->areas) > 0 ? $post->areas[0]->id : null,
            'post_author_id' => $first instanceof SensorTimelinePersistentObject ? $first->attribute(
                'post_author_id'
            ) : null,
            'post_author_group_id' => $first instanceof SensorTimelinePersistentObject ? $first->attribute(
                'post_author_group_id'
            ) : null,
            'post_status' => $status,
            'post_type' => $post->type->identifier,
        ];

        $endAt = $timelineItem->published;
        if ($startAt instanceof DateTime) {
            $duration = $endAt->format('U') - $startAt->format('U');
            $item['start_at'] = $startAt->format(self::DATE_FORMAT);
            $item['duration'] = $duration;
        }

        $row = new self($item);
        $row->store();

        return $row;
    }

    private static function getParentAndChildCategory($categoryId)
    {
        $data = [
            'parent' => null,
            'child' => null,
        ];

        if (is_numeric($categoryId)){
            $item = OpenPaSensorRepository::instance()->getCategoriesTree()->findById($categoryId);
            if ($item instanceof TreeNodeItem){
                $parentId = false;
                $parent = $item->getParent();
                if ($parent && $parent->attribute('type') == 'sensor_category'){
                    $parentId = (int)$parent->attribute('id');
                }
                $data['parent'] = $parentId ? $parentId : $item->attribute('id');
                $data['child'] = $item->attribute('id');
            }
        }

        return $data;
    }

    private static function storeCategories()
    {
        $db = eZDB::instance();
        $tree = TreeNode::walk(OpenPaSensorRepository::instance()->getCategoriesRootNode(), ['language' => 'ita-IT']);
        $insertValues = [];
        foreach ($tree->attribute('children') as $item){
            $insertValues[] = '(' . $item->attribute('id') . ',\'' . $db->escapeString($item->attribute('name')) . '\', null)';
            foreach ($item->attribute('children') as $child){
                $insertValues[] = '(' . $child->attribute('id') . ',\'' . $db->escapeString($child->attribute('name')) . '\',' . $item->attribute('id') . ')';
            }
        }

        $db->query('TRUNCATE ocsensor_category');
        $query = "INSERT INTO ocsensor_category (id, name, parent_id) VALUES " . implode(',', $insertValues);
        $db->query($query);
    }

    private static function storeAreas()
    {
        $db = eZDB::instance();
        $tree = TreeNode::walk(OpenPaSensorRepository::instance()->getAreasRootNode(), ['language' => 'ita-IT']);
        $insertValues = [];
        foreach ($tree->attribute('children') as $item){
            $insertValues[] = '(' . $item->attribute('id') . ',\'' . $db->escapeString($item->attribute('name')) . '\')';
            foreach ($item->attribute('children') as $child){
                $insertValues[] = '(' . $child->attribute('id') . ',\'' . $db->escapeString($child->attribute('name')) . '\')';
            }
        }

        $db->query('TRUNCATE ocsensor_area');
        $query = "INSERT INTO ocsensor_area (id, name) VALUES " . implode(',', $insertValues);
        $db->query($query);
    }

    private static function storeGroups()
    {
        $db = eZDB::instance();
        $tree = TreeNode::walk(OpenPaSensorRepository::instance()->getGroupsRootNode(), ['language' => 'ita-IT']);
        $insertValues = [];
        foreach ($tree->attribute('children') as $item){
            $insertValues[] = '(' . $item->attribute('id') . ',\'' . $db->escapeString($item->attribute('name')) . '\',\'' . $db->escapeString($item->attribute('group')) . '\',\'' . $db->escapeString($item->attribute('reference')) . '\')';
            foreach ($item->attribute('children') as $child){
                $insertValues[] = '(' . $child->attribute('id') . ',\'' . $db->escapeString($child->attribute('name')) . '\',\'' . $db->escapeString($child->attribute('group')) . '\',\'' . $db->escapeString($child->attribute('reference')) . '\')';
            }
        }

        foreach (OpenPaSensorRepository::instance()->getMembersAvailableGroups() as $id => $group){
            $insertValues[] = '(' . $id . ',\'' . $db->escapeString($group['name']) . '\',\'\',\'\')';
        }

        $db->query('TRUNCATE ocsensor_group');
        $query = "INSERT INTO ocsensor_group (id, name, tag, reference) VALUES " . implode(',', $insertValues);
        $db->query($query);
    }

    private static function storeOperators()
    {
        $db = eZDB::instance();
        $tree = TreeNode::walk(OpenPaSensorRepository::instance()->getOperatorsRootNode(), ['language' => 'ita-IT']);
        $insertValues = [];
        foreach ($tree->attribute('children') as $item){
            $insertValues[] = '(' . $item->attribute('id') . ',\'' . $db->escapeString($item->attribute('name')) . '\')';
            foreach ($item->attribute('children') as $child){
                $insertValues[] = '(' . $child->attribute('id') . ',\'' . $db->escapeString($child->attribute('name')) . '\')';
            }
        }

        $db->query('TRUNCATE ocsensor_operator');
        $query = "INSERT INTO ocsensor_operator (id, name) VALUES " . implode(',', $insertValues);
        $db->query($query);
    }

    private static function storeUsers()
    {
        $db = eZDB::instance();
        $viewQuery = "
        CREATE MATERIALIZED VIEW IF NOT EXISTS ocsensor_user
            AS
            SELECT DISTINCT ezcontentobject.id, ezcontentobject.published, ezcontentobject_name.name
            FROM ezcontentobject_tree
              INNER JOIN ezcontentobject ON (ezcontentobject_tree.contentobject_id = ezcontentobject.id)
              INNER JOIN ezcontentobject_name ON ( ezcontentobject_tree.contentobject_id = ezcontentobject_name.contentobject_id AND ezcontentobject_tree.contentobject_version = ezcontentobject_name.content_version)
            WHERE
             ezcontentobject.contentclass_id = 4 AND ezcontentobject_tree.node_id = ezcontentobject_tree.main_node_id AND ezcontentobject.language_mask & 15 > 0 AND ezcontentobject_tree.path_string like '/1/5/12/%'
            ORDER BY ezcontentobject.id asc
        ";
        $db->query($viewQuery);
        $db->query('CREATE UNIQUE INDEX IF NOT EXISTS ocsensor_user_idx ON ocsensor_user (id);');
        $db->query('REFRESH MATERIALIZED VIEW CONCURRENTLY ocsensor_user');
    }

    public static function storeHelperTables($identifiers = [])
    {
        if (!self::isSchemaInstalled()){
            return false;
        }

        if (in_array('categories', $identifiers)) {
            SensorTimelinePersistentObject::storeCategories();
        } elseif (in_array('areas', $identifiers)) {
            SensorTimelinePersistentObject::storeAreas();
        } elseif (in_array('groups', $identifiers)) {
            SensorTimelinePersistentObject::storeGroups();
        } elseif (in_array('operators', $identifiers)) {
            SensorTimelinePersistentObject::storeOperators();
        } elseif (in_array('users', $identifiers)) {
            SensorTimelinePersistentObject::storeUsers();
        } elseif (empty($identifiers)) {
            SensorTimelinePersistentObject::storeCategories();
            SensorTimelinePersistentObject::storeAreas();
            SensorTimelinePersistentObject::storeGroups();
            SensorTimelinePersistentObject::storeOperators();
            SensorTimelinePersistentObject::storeUsers();
        }
    }
}