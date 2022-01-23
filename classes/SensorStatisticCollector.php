<?php

class SensorStatisticCollector
{
    private static $instance;

    private $isEnabled;

    /**
     * @var SensorStatisticStorageInterface
     */
    private $storage;

    private function __construct()
    {
        $sensorIni = eZINI::instance('ocsensor.ini');
        $this->isEnabled = $sensorIni->variable('StatisticCollector', 'UseCustomCollector') == 'enabled';
        $storage = $sensorIni->variable('StatisticCollector', 'Storage');
        if ($storage === 'pdo'){
            $connection = $sensorIni->variable('StatisticCollector', 'PDOStorageConnection');
            $this->storage = new SensorStatisticStoragePDO($connection);
        }
    }

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new SensorStatisticCollector();
        }

        return self::$instance;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled && $this->storage instanceof SensorStatisticStorageInterface;
    }

    public function collect(SensorStatisticPost $statisticPost)
    {
        if ($this->isEnabled){
            $this->storage->upsert($statisticPost);
        }
    }

    public function remove($statisticPostId)
    {
        if ($this->isEnabled){
            $this->storage->delete($statisticPostId);
        }
    }
}
