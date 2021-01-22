<?php

use League\Event\AbstractListener;
use League\Event\EventInterface;
use Opencontent\Sensor\Api\Values\Event as SensorEvent;
use Opencontent\Sensor\Legacy\Repository;

class SensorWebHookListener extends AbstractListener
{
    private $repository;

    private $events;

    private $postSerializer;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
        foreach ($this->repository->getNotificationService()->getNotificationTypes() as $notificationType){
            if (strpos($notificationType->identifier, 'on_') === 0) {
                $this->events[] = $notificationType->identifier;
            }
        }
        if (!in_array('on_add_response', $this->events)) {
            $this->events[] = 'on_add_response';
        }
    }

    public function handle(EventInterface $event, $param = null)
    {
        if ($param instanceof SensorEvent){
            if (in_array($param->identifier, $this->events)){
                $this->repository->getLogger()->info("Emit '{$param->identifier}' to webhook on post {$param->post->id}");
                OCWebHookEmitter::emit(
                    $param->identifier,
                    $param->post,
                    OCWebHookQueue::defaultHandler()
                );
            }

        }
    }

}