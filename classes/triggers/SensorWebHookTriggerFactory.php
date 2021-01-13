<?php

class SensorWebHookTriggerFactory implements OCWebHookTriggerFactoryInterface
{
    public function getTriggers()
    {
        $repository = OpenPaSensorRepository::instance();

        $triggers = [];
        foreach ($repository->getNotificationService()->getNotificationTypes() as $notificationType){
            if (strpos($notificationType->identifier, 'on_') === 0) {
                $triggers[] = new SensorWebHookTrigger($notificationType->identifier, $notificationType->name);
            }
        }

        $triggers[] = new SensorConnectorSenderTrigger();
        $triggers[] = new SensorConnectorReceiverTrigger();

        return $triggers;
    }

}