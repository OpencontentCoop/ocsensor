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
                if ($param->identifier === 'on_create') {
                    SensorTimelinePersistentObject::createOnPublishNewPost($param->post);
                } elseif ($param->identifier === 'on_create_timeline') {
                    SensorTimelinePersistentObject::createOnNewTimelineItem(
                        $param->post,
                        $param->parameters['message']
                    );
                }
            }
        } catch (Exception $e) {
            eZDebug::writeError($e->getMessage(), __METHOD__);
        }
    }

    public function refreshHelpers($identifiers = [])
    {
        try {
            SensorTimelinePersistentObject::storeHelperTables($identifiers);
        } catch (Exception $e) {
            eZDebug::writeError($e->getMessage(), __METHOD__);
        }
    }
}