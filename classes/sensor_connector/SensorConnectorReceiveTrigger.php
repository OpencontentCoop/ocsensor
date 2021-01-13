<?php


class SensorConnectorReceiverTrigger implements OCWebHookTriggerInterface
{
    const IDENTIFIER = 'sensor_connector_receive';

    public function getIdentifier()
    {
        return self::IDENTIFIER;
    }

    public function getName()
    {
        return 'OpenSegnalazioni Connector Receiver';
    }

    public function getDescription()
    {
        return
            'Riceve le segnalazioni da un\'istanza remota di OpenSegnalazioni e ne invia le informazioni di risposta alle segnalazioni ' .
            'Viene scatenato quando è una segnalazione creata con OpenSegnalazioni Connector Sender viene chiusa. ' .
            'Utilizzabile con un endpoint di un\'istanza remota di OpenSegnalazioni /api/sensor_connector/ ' .
            'con OpenSegnalazioni Connector Sender configurato e con cui deve condividere il secret. ' .
            'Occorre inoltre impostare in headers X-Webhook-Trigger: <username-operatore-remoto>';
    }

    public function canBeEnabled()
    {
        return true;
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