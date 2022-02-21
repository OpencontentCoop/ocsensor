<?php

class SensorNotificationTypeOverride
{
    private static $instance;

    private $siteData;

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new SensorNotificationTypeOverride();
        }

        return self::$instance;
    }

    private function __construct()
    {

    }

    /**
     * @return eZSiteData
     */
    private function getSiteData()
    {
        $siteDataName = 'sensor_notification_type_override';
        $siteData = eZSiteData::fetchByName($siteDataName);
        if (!$siteData) {
            $siteData = eZSiteData::create($siteDataName, json_encode([]));
            $siteData->store();
        }
        return $siteData;
    }

    private function storeSiteDataValue(array $data)
    {
        $this->getSiteData()->setAttribute('value', json_encode($data));
        $this->getSiteData()->store();
    }

    public function setOverride($target, $notification, $role)
    {
        $override = json_decode($this->getSiteData()->attribute('value'), true);
        $override[$notification][$role][$target] = $target;
        $this->storeSiteDataValue($override);
    }

    public function unsetOverride($target, $notification, $role)
    {
        $override = json_decode($this->getSiteData()->attribute('value'), true);
        unset($override[$notification][$role][$target]);
        $this->storeSiteDataValue($override);
    }
}
