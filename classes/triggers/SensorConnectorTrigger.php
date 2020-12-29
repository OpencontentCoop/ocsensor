<?php


class SensorConnectorTrigger implements OCWebHookTriggerInterface
{
    const IDENTIFIER = 'sensor_connector';

    public function getIdentifier()
    {
        return self::IDENTIFIER;
    }

    public function getName()
    {
        return 'OpenSegnalazioni Connector';
    }

    public function getDescription()
    {
            return
                'Viene scatenato in tutti gli eventi di OpenSegnalazioni: il payload è un oggetto json Sensor Event (event, post, user, parameters). ' .
                'Utilizzabile con un endpint /api/sensor_connector/ verso un\'istanza di OpenSegnalazioni con OpenSegnalazioni Connector abilitato e configurato';
    }

    public function canBeEnabled()
    {
        return SensorConnectorConfigurationFactory::instance()->isEnabled();
    }

    public function useFilter()
    {
        return false;
    }

    public function isValidPayload($payload, $filters)
    {
        return true;
    }

}