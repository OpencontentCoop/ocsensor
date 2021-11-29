<?php

trait CustomStatAccessTrait
{
    protected function getStats()
    {
        $data = [];
        foreach (OpenPaSensorRepository::instance()->getStatisticsService()->getStatisticFactories(true) as $factory) {
            $data[$factory->getIdentifier()] = $factory->getName();
        }

        return $data;
    }

    protected function getStatData($userId)
    {
        $data = [];
        $sensorStatisticAccess = SensorStatisticAccess::instance();
        foreach (OpenPaSensorRepository::instance()->getStatisticsService()->getStatisticFactories(true) as $factory) {
            if ($sensorStatisticAccess->hasAccessToStat($userId, $factory->getIdentifier())){
                $data[] = $factory->getIdentifier();
            }
        }

        return $data;
    }

    protected function revokeAllStatData($userId)
    {
        $sensorStatisticAccess = SensorStatisticAccess::instance();
        foreach (OpenPaSensorRepository::instance()->getStatisticsService()->getStatisticFactories(true) as $factory) {
            $sensorStatisticAccess->revokeAccessToStat($userId, $factory->getIdentifier());
        }
    }

    protected function grantStatData($userId, $statisticIdentifierList)
    {
        $sensorStatisticAccess = SensorStatisticAccess::instance();
        $this->revokeAllStatData($userId);
        foreach (OpenPaSensorRepository::instance()->getStatisticsService()->getStatisticFactories(true) as $factory) {
            if (in_array($factory->getIdentifier(), $statisticIdentifierList)) {
                $sensorStatisticAccess->assignAccessToStat($userId, $factory->getIdentifier());
            }
        }
    }
}
