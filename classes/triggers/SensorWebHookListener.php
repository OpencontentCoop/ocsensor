<?php

use League\Event\AbstractListener;
use League\Event\EventInterface;
use Opencontent\Sensor\Api\Values\Event as SensorEvent;
use Opencontent\Sensor\Legacy\Repository;
use Opencontent\Sensor\OpenApi;
use Opencontent\Sensor\OpenApi\PostSerializer;

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
        $siteUrl = '/';
        eZURI::transformURI($siteUrl,true, 'full');
        $endpointUrl = '/api/sensor';
        eZURI::transformURI($endpointUrl, true, 'full');
        $openApiTools = new OpenApi(
            $this->repository,
            $siteUrl,
            $endpointUrl
        );
        $this->postSerializer = new PostSerializer($openApiTools);
    }

    public function handle(EventInterface $event, $param = null)
    {
        if ($param instanceof SensorEvent){
            if (in_array($param->identifier, $this->events)){
                $this->repository->getLogger()->info("Send webhook '{$param->identifier}' on post {$param->post->id}");
                $payload = $this->postSerializer->serialize($param->post);
                OCWebHookEmitter::emit(
                    $param->identifier,
                    $payload,
                    OCWebHookQueue::defaultHandler()
                );
            }

        }
    }

}