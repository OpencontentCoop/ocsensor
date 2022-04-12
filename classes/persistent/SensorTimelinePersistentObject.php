<?php

use Opencontent\Sensor\Api\Values\Message\TimelineItem;
use Opencontent\Sensor\Api\Values\Post;
use Opencontent\Sensor\Legacy\Utils\TreeNodeItem;

class SensorTimelinePersistentObject extends eZPersistentObject
{
    const DATE_FORMAT = 'Y-m-d H:i:s';

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

    private function convertTimestamp($timestamp)
    {
        return date(self::DATE_FORMAT, $timestamp);
    }

    public static function createOnPublishNewPost(Post $post)
    {
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

    public function toArray()
    {
        $data = [];
        foreach (array_keys(self::definition()['fields']) as $field){
            $data[$field] = $this->attribute($field);
        }

        return $data;
    }
}