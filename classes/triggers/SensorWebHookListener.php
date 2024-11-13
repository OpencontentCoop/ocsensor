<?php

use League\Event\AbstractListener;
use League\Event\EventInterface;
use Opencontent\Sensor\Api\Values\Event as SensorEvent;
use Opencontent\Sensor\Legacy\Repository;

class SensorWebHookListener extends AbstractListener
{
    private $repository;

    private $events;

    private $userBlackList = [];

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
        foreach ($this->repository->getNotificationService()->getNotificationTypes() as $notificationType) {
            if (strpos($notificationType->identifier, 'on_') === 0) {
                $this->events[] = $notificationType->identifier;
            }
        }
        if (!in_array('on_add_response', $this->events)) {
            $this->events[] = 'on_add_response';
        }
        if (!in_array('on_add_attachment', $this->events)) {
            $this->events[] = 'on_add_attachment';
        }
        if (!in_array('on_moderate', $this->events)) {
            $this->events[] = 'on_moderate';
        }
        $this->userBlackList = (array)$this->repository->getSensorSettings()->get('WebhookUserEmailBlackList');
    }

    public function handle(EventInterface $event, $param = null)
    {
        if ($param instanceof SensorEvent) {
            if (in_array($param->identifier, $this->events)) {
                if (in_array($this->repository->getCurrentUser()->email, $this->userBlackList)) {
                    $this->repository->getLogger()->debug(
                        "Webhook '{$param->identifier}' disallowed for black listed user "
                        . $this->repository->getCurrentUser()->email . " on post {$param->post->id}"
                    );
                } else {
                    $this->repository->getLogger()->info(
                        "Emit '{$param->identifier}' to webhook on post {$param->post->id}"
                    );

                    $trigger = OCWebHookTriggerRegistry::registeredTrigger($param->identifier);
                    if ($trigger instanceof OCWebHookTriggerInterface) {
                        $webHooks = OCWebHook::fetchEnabledListByTrigger($trigger->getIdentifier());
                        foreach ($webHooks as $index => $webHook) {
                            $filters = null;
                            if ($trigger->useFilter()) {
                                $currentTriggers = $webHook->getTriggers();
                                foreach ($currentTriggers as $currentTrigger) {
                                    if ($currentTrigger['identifier'] == $trigger->getIdentifier()) {
                                        $filters = $currentTrigger['filters'];
                                    }
                                }
                            }
                            if ($trigger->isValidPayload($param->post, $filters)) {
                                $this->repository->getPostService()->doRefreshPost($param->post);
                                break;
                            }
                        }
                    }

                    OCWebHookEmitter::emit(
                        $param->identifier,
                        $param->post,
                        OCWebHookQueue::defaultHandler()
                    );
                }
            }
        }
    }

}