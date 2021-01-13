<?php

use Opencontent\Sensor\Api\Values\Operator;
use Opencontent\Sensor\Api\Values\Participant;
use Opencontent\Sensor\OpenApi;
use Opencontent\Sensor\OpenApi\PostSerializer;

class SensorConnectorSenderTrigger implements OCWebHookTriggerInterface
{
    const IDENTIFIER = 'sensor_connector_send';

    protected static $schema;

    protected $repository;

    protected $postSerializer;

    public function __construct()
    {
        $this->repository = OpenPaSensorRepository::instance();
        $siteUrl = '/';
        eZURI::transformURI($siteUrl, true, 'full');
        $endpointUrl = '/api/sensor';
        eZURI::transformURI($endpointUrl, true, 'full');
        $openApiTools = new OpenApi(
            $this->repository,
            rtrim($siteUrl, '/'),
            $endpointUrl
        );
        $this->postSerializer = new PostSerializer($openApiTools);
    }

    public function getIdentifier()
    {
        return self::IDENTIFIER;
    }

    public function getName()
    {
        return 'OpenSegnalazioni Connector Sender';
    }

    public function getDescription()
    {
        return
            'Crea segnalazioni in un\'istanza remota di OpenSegnalazioni e ne riceve i messaggi di chiusura ' .
            'Viene scatenato quando è coinvolto un operatore configurato nei filtri. ' .
            'Utilizzabile con un endpoint di un\'istanza remota di OpenSegnalazioni /api/sensor_connector/ ' .
            'con OpenSegnalazioni Connector Receiver configurato e con cui deve condividere il secret. ' .
            'Occorre inoltre impostare in headers X-Webhook-Trigger: <username-utente-remoto>';
    }

    public function canBeEnabled()
    {
        return true;
    }

    public function useFilter()
    {
        if (self::$schema === null) {

            $operators = [];
            $this->loadAllOperators($this->repository, '*', $operators);

            $operatorIdNameList = [];
            /** @var Operator $operator */
            foreach ($operators as $operator) {
                $operatorIdNameList[$operator->id] = str_replace("'", "&apos;", $operator->attribute('name'));
            }

            $schema = [
                'schema' => [
                    'title' => "Il webhook viene eseguito quando si verificano tutte le condizioni di seguito indicate",
                    'type' => 'object',
                    'properties' => [
                        'operator' => [
                            'title' => "Seleziona operatore",
                            'type' => 'string',
                            'enum' => array_keys($operatorIdNameList),
                        ],
                    ],
                ],
                'options' => [
                    'fields' => [
                        'operator' => [
                            'helper' => "Il webhook viene eseguito solo se la segnalazione coinvolge uno degli operatori selezionati",
                            'optionLabels' => array_values($operatorIdNameList),
                            'type' => 'checkbox',
                        ],
                    ],
                ]
            ];

            self::$schema = json_encode($schema);
        }

        return self::$schema;
    }

    private function loadAllOperators(OpenPaSensorRepository $repository, $cursor, &$operators)
    {
        $results = $repository->getOperatorService()->loadOperators(false, \Opencontent\Sensor\Api\SearchService::MAX_LIMIT, $cursor);
        $operators = array_merge($operators, $results['items']);
        if ($results['next'] && $results['next'] != $cursor) {
            loadAllOperators($repository, $results['next'], $operators);
        }
    }

    public function isValidPayload($payload, $filters)
    {
        try {
            $event = $payload['sensor_event'];
            $postId = $payload['sensor_post']->id;
            $post = $payload['sensor_post'];
            $triggerName = self::IDENTIFIER;

            $filters = json_decode($filters, true);
            $operatorIdList = [];
            if (isset($filters['operator'])) {
                $operatorIdList = explode(',', $filters['operator']);
            }
            if (empty($operatorIdList)) {
                return false;
            }

            // avoid loop with receiver
            if (in_array($this->repository->getCurrentUser()->id, $operatorIdList)) {
                return false;
            }

            if ($event == 'on_assign') {
                if (isset($payload['sensor_event_parameters']['owners'])) {
                    foreach ($payload['sensor_event_parameters']['owners'] as $owner) {
                        if (in_array($owner, $operatorIdList)) {
                            $payload['event'] = SensorConnector::EVENT_OPEN;
                            $payload['post'] = $this->postSerializer->serialize($post);
                            $this->log($event, $postId, "[$triggerName] Current operator is owner");
                            return true;
                        }
                    }
                }
                foreach ($post->observers->getParticipantIdListByType(Participant::TYPE_USER) as $userId) {
                    if (in_array($userId, $operatorIdList)) {
                        $payload['event'] = SensorConnector::EVENT_CLOSE;
                        $payload['post'] = $this->postSerializer->serialize($post);
                        $this->log($event, $postId, "[$triggerName] Operator was owner");
                        return true;
                    }
                }
            } elseif ($event == 'on_group_assign' || $event == 'auto_assign') {
                foreach ($post->observers->getParticipantIdListByType(Participant::TYPE_USER) as $userId) {
                    if (in_array($userId, $operatorIdList)) {
                        $payload['event'] = SensorConnector::EVENT_CLOSE;
                        $payload['post'] = $this->postSerializer->serialize($post);
                        $this->log($event, $postId, "[$triggerName] Operator was owner");
                        return true;
                    }
                }
            } elseif ($event == 'on_fix' || $event == 'on_close') {
                foreach ($post->observers->getParticipantIdListByType(Participant::TYPE_USER) as $userId) {
                    if (in_array($userId, $operatorIdList)) {
                        $payload['event'] = SensorConnector::EVENT_CLOSE;
                        $payload['post'] = $this->postSerializer->serialize($post);
                        $this->log($event, $postId, "[$triggerName] Operator was owner or observer");
                        return true;
                    }
                }
            }

        } catch (Exception $e) {
            $this->log($event, $postId, $e->getMessage());
        }

        return false;
    }

    private function log($event, $postId, $message)
    {
        eZLog::write("[$postId] [$event] $message", 'sensor_connector.log');
    }

}