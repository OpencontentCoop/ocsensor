<?php


class SensorConnectorConfigurationFactory
{
    private static $instance;

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

    public function getConfigurationBySignature($signature, array $payload)
    {
        $configurations = $this->getConfigurations();
        foreach ($configurations as $configurationData){
            $configuration = new SensorConnectorConfiguration($configurationData);
            if ($configuration->isValidPayload($signature, $payload)){
                return $configuration;
            }
        }

        throw new Exception("Sensor connect configuration not found");
    }

    public function getConfigurationByIdentifier($identifier)
    {
        $configurations = $this->getConfigurations();
        if (isset($configurations[$identifier])){
            return new SensorConnectorConfiguration($configurations[$identifier]);
        }

        throw new Exception("Sensor connect configuration not found");
    }

    public function getConfigurations()
    {

return [
    'prova' => [
        'identifier' => 'prova',
        'secret' => 'abcd',
        'userId' => 148
    ],
    'test' => [
        'identifier' => 'test',
        'secret' => '123456',
        'userId' => 148
    ],
];

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
        $configurations[$configuration->getIdentifier()] = $configuration;
        $this->storeConfigurations($configurations);
    }
}