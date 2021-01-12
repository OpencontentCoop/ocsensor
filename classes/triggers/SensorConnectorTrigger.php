<?php

use Opencontent\Sensor\Api\Values\Operator;
use Opencontent\Sensor\Api\Values\Participant;

class SensorConnectorTrigger implements OCWebHookTriggerInterface
{
    const IDENTIFIER = 'sensor_connector';

    protected static $schema;

    protected $repository;

    public function __construct()
    {
        $this->repository = OpenPaSensorRepository::instance();
    }

    public function getIdentifier()
    {
        return self::IDENTIFIER;
    }

    public function getName()
    {
        return 'OpenSegnalazioni Connector';
    }

    public function getDescription()
    {
        return
            'Viene scatenato quando è coinvolto un operatore selezionato: il payload è un oggetto json Sensor Event (event, post, user, parameters). ' .
            'Utilizzabile con un endpoint /api/sensor_connector/ verso un\'istanza di OpenSegnalazioni con OpenSegnalazioni Connector abilitato e configurato.';
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
            $event = $payload['event'];
            $postId = $payload['post']['id'];
            $post = $this->repository->getPostService()->loadPost($postId);

            $filters = json_decode($filters, true);
            $operatorIdList = [];
            if (isset($filters['operator'])) {
                $operatorIdList = explode(',', $filters['operator']);
            }
            if (empty($operatorIdList)) {
                $this->log($event, $postId, "Empty operator list");
                return false;
            }

            if ($event == 'on_assign') {
                foreach ($payload['parameters']['owners'] as $owner) {
                    if (in_array($owner, $operatorIdList)) {
                        $this->log($event, $postId, "[SEND] Is owner");
                        return true;
                    }
                }
                foreach ($post->observers->getParticipantIdListByType(Participant::TYPE_USER) as $userId) {
                    if (in_array($userId, $operatorIdList)) {
                        $payload['event'] = 'change_owner';
                        $this->log($event, $postId, "[SEND] Was owner");
                        return true;
                    }
                }
            }elseif ($event == 'on_group_assign' || $event == 'auto_assign') {
                foreach ($post->observers->getParticipantIdListByType(Participant::TYPE_USER) as $userId) {
                    if (in_array($userId, $operatorIdList)) {
                        $payload['event'] = 'change_owner';
                        $this->log($event, $postId, "[SEND] Was owner");
                        return true;
                    }
                }
            } elseif ($event == 'on_fix' || $event == 'on_close') {
                foreach ($post->observers->getParticipantIdListByType(Participant::TYPE_USER) as $userId) {
                    if (in_array($userId, $operatorIdList)) {
                        $this->log($event, $postId, "[SEND] Was owner or observer");
                        return true;
                    }
                }
            }

        } catch (Exception $e) {
            $this->log($event, $postId, $e->getMessage());
        }

        $this->log($event, $postId, '');
        return false;
    }

    private function log($event, $postId, $message)
    {
        eZLog::write("[$postId][$event] $message", 'sensor_connector.log');
    }

}