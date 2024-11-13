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
        $triggers[] = new SensorWebHookTrigger(
            'on_add_response',
            SensorTranslationHelper::instance()->translate('Publication of an official response')
        );
        $triggers[] = new SensorWebHookTrigger(
            'on_add_attachment',
            SensorTranslationHelper::instance()->translate('Add an attachment')
        );
        $triggers[] = new SensorWebHookTrigger(
            'on_moderate',
            SensorTranslationHelper::instance()->translate('Moderate post')
        );

        return $triggers;
    }

}
