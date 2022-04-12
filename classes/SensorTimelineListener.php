<?php

use League\Event\AbstractListener;
use League\Event\EventInterface;
use Opencontent\Sensor\Api\Values\Event as SensorEvent;

class SensorTimelineListener extends AbstractListener
{
    public function handle(EventInterface $event, $param = null)
    {
        try {
            if ($param instanceof SensorEvent) {
                $item = false;
                if ($param->identifier === 'on_create') {
                    $item = SensorTimelinePersistentObject::createOnPublishNewPost($param->post);
                } elseif ($param->identifier === 'on_create_timeline') {
                    $item = SensorTimelinePersistentObject::createOnNewTimelineItem(
                        $param->post,
                        $param->parameters['message']
                    );
                }
                if ($item instanceof SensorTimelinePersistentObject) {
                    (new SensorSearchableTimelineRepository())->index(new SensorSearchableTimeline($item->toArray()));
                }
            }
        }catch (Exception $e){
            eZDebug::writeError($e->getMessage(), __METHOD__);
        }
    }
}