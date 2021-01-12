<?php

use League\Event\EventInterface;
use Opencontent\Sensor\Api\Values\Event as SensorEvent;

class SensorConnectorListener extends SensorWebHookListener
{
    public function handle(EventInterface $event, $param = null)
    {
        if ($param instanceof SensorEvent){

            if (!in_array($param->identifier, [
                'on_assign',
                'on_group_assign',
                'auto_assign',
                'on_fix',
                'on_close',
            ])){
                return false;
            }

            $payload = new ArrayObject([
                'event' => $param->identifier,
                'post' => $this->postSerializer->serialize($param->post),
                'user' => $this->userSerializer->serialize($param->user),
                'parameters' => $param->parameters,
            ]);
            OCWebHookEmitter::emit(
                SensorConnectorTrigger::IDENTIFIER,
                $payload,
                OCWebHookQueue::defaultHandler()
            );
        }
    }
}