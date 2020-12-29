<?php


class SensorConnectorConfigurationFactory
{
    private static $instance;

    private $isEnabled;

    private function __construct()
    {
    }

    public static function instance()
    {
        if (self::$instance === null){
            self::$instance = new SensorConnectorConfigurationFactory();
        }

        return self::$instance;
    }

    public function isEnabled()
    {
        return $this->isEnabled;
    }

    public function getConfiguration($identifier)
    {
        $configurations = $this->getConfigurations();
        if (isset($configurations[$identifier])){
            return new SensorConnectorConfiguration($configurations[$identifier]);
        }

        return new SensorConnectorConfiguration([]);
        //throw new Exception("Configuration $identifier not found");
    }

    public function getConfigurations()
    {
        $data = eZSiteData::fetchByName('sensor_connector');
        if (!$data instanceof eZSiteData){
            $data = new eZSiteData([
                'name' => 'sensor_connector',
                'value' => json_encode([])
            ]);
            $data->store();
        }

        return json_decode($data->attribute('value'), true);
    }

    private function storeConfigurations($configurations)
    {
        $data = eZSiteData::fetchByName('sensor_connector');
        if (!$data instanceof eZSiteData){
            $data = new eZSiteData([
                'name' => 'sensor_connector',
                'value' => json_encode([])
            ]);
        }
        $data->setAttribute('value', json_encode($configurations));
        $data->store();
    }

    public function addConfiguration(SensorConnectorConfiguration $configuration)
    {
        $configurations = $this->getConfigurations();
        $configurations[$configuration->identifier] = $configuration;
        $this->storeConfigurations($configurations);
    }
}