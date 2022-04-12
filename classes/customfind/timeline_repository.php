<?php

class SensorSearchableTimelineRepository extends OCCustomSearchableRepositoryAbstract
{
    use StatsPivotRepository;

    public function getIdentifier()
    {
        return 'sensor_timeline';
    }

    public function availableForClass()
    {
        return 'SensorSearchableTimeline';
    }

    public function countSearchableObjects()
    {
        return (int)SensorTimelinePersistentObject::count(SensorTimelinePersistentObject::definition());
    }

    public function fetchSearchableObjectList($limit, $offset)
    {
        $rows = SensorTimelinePersistentObject::fetchObjectList(
            SensorTimelinePersistentObject::definition(), null, null,
            ['timeline_id' => 'asc', 'post_id' => 'asc'],
            ['limit' => $limit, 'offset' => $offset]
        );
        $data = [];
        foreach ($rows as $row){
            $data[] = new SensorSearchableTimeline($row->toArray());
        }

        return $data;
    }

    public function fetchSearchableObject($objectID)
    {
        [$timelineId, $postId] = explode('-', str_replace('timeline-', '', $objectID));
        $timelineObject = SensorTimelinePersistentObject::fetchObject(
            SensorTimelinePersistentObject::definition(), null,
            ['timeline_id' => (int)$timelineId, 'post_id' => (int)$postId]
        );
        if ($timelineObject instanceof SensorTimelinePersistentObject){
            return new SensorSearchableTimeline($timelineObject->toArray());
        }

        return null;
    }
}