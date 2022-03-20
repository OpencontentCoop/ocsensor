<?php

use Opencontent\Sensor\Api\Values\Message\TimelineItem;
use Opencontent\Sensor\Api\Values\Post;
/*
SELECT count(distinct post_id), status FROM (
SELECT DISTINCT ON (post_id) post_id, start_at, status from ocsensor_timeline ORDER BY post_id, end_at DESC
) AS t group by status;

CREATE MATERIALIZED VIEW ocsensor_status
AS
SELECT DISTINCT ON (post_id) * from ocsensor_timeline ORDER BY post_id, end_at DESC
WITH NO DATA;
CREATE UNIQUE INDEX ocsensor_status_idx ON ocsensor_status (post_id, timeline_id);

REFRESH MATERIALIZED VIEW CONCURRENTLY ocsensor_status;
SELECT count(distinct post_id), category_id FROM ocsensor_status group by category_id;


SELECT
  category_id,
  status,
  count(distinct post_id)
FROM
    ocsensor_status
--WHERE category_id = 644
GROUP BY
  status,
  category_id
ORDER BY
    category_id;

SELECT status, count(distinct post_id) FROM (
  SELECT DISTINCT ON (post_id) post_id, start_at, status from ocsensor_timeline
  WHERE end_at between '2020-01-01' AND '2023-01-01'
  ORDER BY post_id, end_at DESC
) AS t group by status;

SELECT status, count(distinct post_id) FROM (
  SELECT DISTINCT ON (post_id) post_id, start_at, status from ocsensor_timeline
  WHERE '[2020-01-01, 2023-01-01]'::tsrange @> end_at
  ORDER BY post_id, end_at DESC
) AS t group by status;
*/

class SensorTimelineIndexer extends eZPersistentObject
{
    const DATE_FORMAT = 'Y-m-d H:i:s';

    public static function indexPublish(Post $post)
    {
        $item = [
            'timeline_id' => 0,
            'creator_id' => $post->reporter ? $post->reporter->id : $post->author->id,
            'post_id' => $post->id,
            'user_id' => $post->author->id,
            'user_group_id' => count($post->author->groups) > 0 ? $post->author->groups[0] : null,
            'execution_type' => 'creating',
            'category_id' => count($post->categories) > 0 ? $post->categories[0]->id : null,
            'area_id' => count($post->areas) > 0 ? $post->areas[0]->id : null,
            'status' => 'pending',
            'end_at' => $post->published->format(self::DATE_FORMAT),
            'post_type' => $post->type->identifier,
        ];
        $row = new self($item);
        $row->store();
    }

    public static function indexTimelineItem(Post $post, TimelineItem $timelineItem)
    {
        $operator = $group = null;
        $extra = $timelineItem->extra;
        if (!empty($extra)) {
            $operatorClassId = eZContentClass::classIDByIdentifier('sensor_operator');
            $groupClassId = eZContentClass::classIDByIdentifier('sensor_group');
            $objects = eZPersistentObject::fetchObjectList(
                eZContentObject::definition(),
                ['id', 'contentclass_id'],
                [
                    'contentclass_id' => [[$groupClassId, $operatorClassId,]],
                    'id' => [$extra],
                ], null, null, false
            );
            foreach ($objects as $object) {
                if ($object['contentclass_id'] == $operatorClassId) {
                    $operator = (int)$object['id'];
                }
                if ($object['contentclass_id'] == $groupClassId) {
                    $group = (int)$object['id'];
                }
            }
        }

        $previous = false;
        /** @var SensorTimelineIndexer[] $previouses */
        $previousList = SensorTimelineIndexer::fetchObjectList(
            SensorTimelineIndexer::definition(),
            null,
            ['post_id' => $post->id, 'timeline_id' => [ '<', $timelineItem->id],],
            ['timeline_id' => 'desc'],
            ['limit' => 1]
        );
        if (count($previousList) > 0) {
            $previous = $previousList[0];
        }

        $executionType = $startAt = $status = false;
        switch($timelineItem->type){
            case 'read':
                $executionType = 'reading';
                $startAt = $post->published;
                $status = 'open';
                break;

            case 'assigned':
                if ($previous instanceof SensorTimelineIndexer){
                    $executionType = 'assigning';
                    $startAt =  DateTime::createFromFormat(self::DATE_FORMAT, $previous->attribute('end_at'));
                    $status = 'open';
                }
                break;

            case 'fixed':
                if ($previous instanceof SensorTimelineIndexer){
                    $executionType = 'fixing';
                    $startAt =  DateTime::createFromFormat(self::DATE_FORMAT, $previous->attribute('end_at'));
                    $status = 'open';
                }
                break;

            case 'closed':
                if ($previous instanceof SensorTimelineIndexer){
                    $executionType = 'closing';
                    $startAt =  DateTime::createFromFormat(self::DATE_FORMAT, $previous->attribute('end_at'));
                    $status = 'close';
                }
                break;

            case 'reopened':
                if ($previous instanceof SensorTimelineIndexer){
                    $executionType = 'reopening';
                    $startAt = DateTime::createFromFormat(self::DATE_FORMAT, $previous->attribute('end_at'));
                    $status = 'pending';
                }
                break;
        }

        if ($previous instanceof SensorTimelineIndexer){
            if (!$group && $operator == $previous->attribute('operator_id')){
                $group = $previous->attribute('group_id');
            }
            if (!$operator){
                $operator = $previous->attribute('operator_id');
            }
        }

        $item = [
            'timeline_id' => $timelineItem->id,
            'creator_id' => $timelineItem->creator->id,
            'post_id' => $post->id,
            'user_id' => $post->author->id,
            'user_group_id' => count($post->author->groups) > 0 ? $post->author->groups[0] : null,
            'operator_id' => $operator,
            'group_id' => $group,
            'execution_type' => $executionType,
            'category_id' => count($post->categories) > 0 ? $post->categories[0]->id : null,
            'area_id' => count($post->areas) > 0 ? $post->areas[0]->id : null,
            'status' => $status,
            'end_at' => $timelineItem->published->format(self::DATE_FORMAT),
            'post_type' => $post->type->identifier,
        ];

        $endAt = $timelineItem->published;
        if ($startAt instanceof DateTime){
            $executionTime = $endAt->format('U') - $startAt->format('U');
            $item['start_at'] = $startAt->format(self::DATE_FORMAT);
            $item['execution_time'] = $executionTime;
        }

        $row = new self($item);
        $row->store();
    }

    public static function definition()
    {
        return [
            'fields' => [
                'timeline_id' => [
                    'name' => 'timeline_id',
                    'datatype' => 'integer',
                ],
                'creator_id' => [
                    'name' => 'creator_id',
                    'datatype' => 'integer',
                    'default' => null,
                ],
                'post_id' => [
                    'name' => 'post_id',
                    'datatype' => 'integer',
                    'default' => null,
                ],
                'user_id' => [
                    'name' => 'user_id',
                    'datatype' => 'integer',
                    'default' => null,
                ],
                'user_group_id' => [
                    'name' => 'user_group_id',
                    'datatype' => 'integer',
                    'default' => null,
                ],
                'operator_id' => [
                    'name' => 'operator_id',
                    'datatype' => 'integer',
                    'default' => null,
                ],
                'group_id' => [
                    'name' => 'group_id',
                    'datatype' => 'integer',
                    'default' => null,
                ],
                'category_id' => [
                    'name' => 'category_id',
                    'datatype' => 'integer',
                    'default' => null,
                ],
                'area_id' => [
                    'name' => 'area_id',
                    'datatype' => 'integer',
                    'default' => null,
                ],
                'execution_type' => [
                    'name' => 'execution_type',
                    'datatype' => 'string',
                    'default' => null,
                ],
                'status' => [
                    'name' => 'status',
                    'datatype' => 'string',
                    'default' => null,
                ],
                'post_type' => [
                    'name' => 'post_type',
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
                'execution_time' => [
                    'name' => 'execution_time',
                    'datatype' => 'integer',
                    'default' => null,
                ],
            ],
            'keys' => ['timeline_id', 'post_id'],
            'class_name' => 'SensorTimelineIndexer',
            'name' => 'ocsensor_timeline',
        ];
    }

    private function convertTimestamp($timestamp)
    {
        return date(self::DATE_FORMAT, $timestamp);
    }
}
