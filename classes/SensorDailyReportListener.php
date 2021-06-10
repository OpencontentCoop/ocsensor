<?php

use League\Event\AbstractListener;
use League\Event\EventInterface;
use Opencontent\Sensor\Api\Values\Event as SensorEvent;

class SensorDailyReportListener extends AbstractListener
{
    public function handle(EventInterface $event, $param = null)
    {
        if ($param instanceof SensorEvent) {
            if (in_array($param->identifier, ['on_create', 'on_close'])) {
                try {
                    $repository = OCCustomSearchableRepositoryProvider::instance()->provideRepository('sensor_daily_report');
                    $item = $repository->fetchSearchableObject('now');
                    if ($item instanceof OCCustomSearchableObjectInterface) {
                        $repository->index($item);
                    }
                } catch (Exception $e) {
                    eZDebug::writeError($e->getMessage(), __METHOD__);
                }
            }

        }
    }

}