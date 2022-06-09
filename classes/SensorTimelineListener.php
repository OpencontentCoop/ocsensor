<?php

use League\Event\AbstractListener;
use League\Event\EventInterface;
use Opencontent\Sensor\Api\Values\Event as SensorEvent;

class SensorTimelineListener extends AbstractListener
{
    private static $isEnabled;

    public function handle(EventInterface $event, $param = null)
    {
        if ($this->isEnabled()) {
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
    }

    public function refreshHelpers($identifiers = [])
    {
        if ($this->isEnabled()) {
            try {
                SensorTimelinePersistentObject::storeHelperTables($identifiers);
            } catch (Exception $e) {
                eZDebug::writeError($e->getMessage(), __METHOD__);
            }
        }
    }

    public function isEnabled()
    {
        if (self::$isEnabled === null){
            self::$isEnabled =  eZINI::instance('ocsensor.ini')->variable('SensorConfig', 'CollectSensorTimelineItems') == 'enabled';
        }
        return self::$isEnabled;
    }
}