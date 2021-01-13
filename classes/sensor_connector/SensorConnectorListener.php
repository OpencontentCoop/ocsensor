<?php

use League\Event\EventInterface;
use Opencontent\Sensor\Api\Values\Event as SensorEvent;

class SensorConnectorListener extends SensorWebHookListener
{
    public function handle(EventInterface $event, $param = null)
    {
        if ($param instanceof SensorEvent) {

            $payload = new ArrayObject([
                'sensor_event' => $param->identifier,
                'sensor_post' => $param->post,
                'sensor_event_parameters' => $param->parameters,
            ]);

            if (in_array($param->identifier, [
                'on_assign',
                'on_group_assign',
                'auto_assign',
                'on_fix',
                'on_close',
            ])) {
                OCWebHookEmitter::emit(
                    SensorConnectorSenderTrigger::IDENTIFIER,
                    $payload,
                    OCWebHookQueue::defaultHandler()
                );
            }

            if ($param->identifier == 'on_close') {
                $configuration = SensorConnectorConfigurationFactory::instance()->getConfigurationByPost($param->post);
                if ($configuration instanceof SensorConnectorConfiguration) {

                    $triggerName = SensorConnectorReceiverTrigger::IDENTIFIER;
                    eZLog::write("[{$param->post->id}] [{$param->identifier}] [{$triggerName}]", 'sensor_connector.log');

                    $payload['event'] = SensorConnector::EVENT_RECEIVE_CLOSE;
                    $payload['post'] = $this->postSerializer->serialize($param->post);
                    $payload['post']['id'] = $configuration->getRemotePostId($param->post);
                    $payload['post']['remote_post_id'] = $param->post->id;
                    $payload['post']['response'] = $this->messageSerializer->serialize($param->post->responses->last());
                    OCWebHookEmitter::emit(
                        $triggerName,
                        $payload,
                        OCWebHookQueue::defaultHandler()
                    );
                }
            }
        }
    }
}