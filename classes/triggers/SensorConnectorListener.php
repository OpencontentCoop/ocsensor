<?php

use League\Event\EventInterface;
use Opencontent\Sensor\Api\Values\Event as SensorEvent;

class SensorConnectorListener extends SensorWebHookListener
{
    public function handle(EventInterface $event, $param = null)
    {
        if ($param instanceof SensorEvent){
            $payload = [
                'event' => $param->identifier,
                'post' => $this->postSerializer->serialize($param->post),
                'user' => $this->userSerializer->serialize($param->user),
                'parameters' => $param->parameters,
            ];
            OCWebHookEmitter::emit(
                SensorConnectorTrigger::IDENTIFIER,
                $payload,
                OCWebHookQueue::defaultHandler()
            );
        }
    }
}