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
        $event = $payload['event'];
        $postId = $payload['post']['id'];

        $filters = json_decode($filters, true);
        $operatorIdList = [];
        if (isset($filters['operator'])) {
            $operatorIdList = explode(',', $filters['operator']);
        }

        if (empty($operatorIdList)) {
            $this->log($event, $postId, "Empty operator list");
            return false;
        }

        if (!in_array($event, [
            'on_assign',
            'on_fix',
            'on_add_observer',
            'on_close'
        ])) {
            $this->log($event, $postId, "Unhandled event");
            return false;
        }

        try {
            $post = $this->repository->getPostService()->loadPost($postId);
            $ownersAndObservers = array_merge(
                $post->owners->getParticipantIdListByType(Participant::TYPE_USER),
                $post->observers->getParticipantIdListByType(Participant::TYPE_USER)
            );
            foreach ($ownersAndObservers as $userId) {
                if (in_array($userId, $operatorIdList)) {
                    $this->log($event, $postId, "Ok: send webhook!");
                    return true;
                }
            }
        } catch (Exception $e) {
            eZDebug::writeError($e->getMessage(), __METHOD__);
        }

        $this->log($event, $postId, "Unhandled operator");
        return false;
    }

    private function log($event, $postId, $message)
    {
        eZLog::write("[$postId][$event] $message", 'sensor_connector.log');
    }

}