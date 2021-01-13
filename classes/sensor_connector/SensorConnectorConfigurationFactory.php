<?php


use Opencontent\Sensor\Api\Values\Post;

class SensorConnectorConfigurationFactory
{
    private static $instance;

    private $configurations;

    private function __construct()
    {
    }

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new SensorConnectorConfigurationFactory();
        }

        return self::$instance;
    }

    public function getConfigurationByTriggerAndSignature($trigger, $signature, array $payload)
    {
        $configurations = $this->getConfigurations();
        foreach ($configurations as $configurationData) {
            $configuration = new SensorConnectorConfiguration($configurationData);
            if ($configuration->getType() == $trigger && $configuration->isValidPayload($signature, $payload)) {
                return $configuration;
            }
        }

        throw new Exception("Can not find sensor connect configuration by signature");
    }

    public function getConfigurations()
    {
        if ($this->configurations === null){
            $this->configurations = [];
            $db = eZDB::instance();
            $webooks = $db->arrayQuery(
                "SELECT * from ocwebhook 
                 INNER JOIN ocwebhook_trigger_link ON (ocwebhook.id = ocwebhook_trigger_link.webhook_id) 
                 WHERE ocwebhook_trigger_link.trigger_identifier IN ('".SensorConnectorSenderTrigger::IDENTIFIER."','".SensorConnectorReceiverTrigger::IDENTIFIER."')
                 AND ocwebhook.enabled = 1"
            );
            foreach ($webooks as $webook){
                $identifier = 'sensor-connector-' . $webook['id'];
                $this->configurations[$identifier] = [
                    'identifier' => $identifier,
                    'secret' => $webook['secret'],
                    'type' => $webook['trigger_identifier'],
                    'endpoint' => $webook['url'],
                ];
            }
        }

        return $this->configurations;
    }

    public function getConfigurationByPost(Post $post)
    {
        $object = eZContentObject::fetch($post->id);
        if ($object instanceof eZContentObject) {
            $remoteId = $object->attribute('remote_id');
            foreach ($this->getConfigurations() as $identifier => $configurationData) {
                if (strpos($remoteId, $identifier) !== false) {
                    return new SensorConnectorConfiguration($configurationData);
                }
            }
        }

        return false;
    }
}