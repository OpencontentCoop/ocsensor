<?php

use Opencontent\Ocopendata\Forms\Connectors\AbstractBaseConnector;
use Opencontent\Sensor\Legacy\Utils\TreeNode;

class OperatorSettingsConnector extends AbstractBaseConnector
{
    use CustomStatAccessTrait;
    use MemberGroupsTrait;

    private static $isLoaded;

    /**
     * @var eZUser
     */
    protected $user;

    /**
     * @var \Opencontent\Sensor\Api\Values\User
     */
    private $sensorUser;

    /**
     * @var OpenPaSensorRepository
     */
    private $repository;

    private $availableGroups = [];

    private $groups = [];

    public function runService($serviceIdentifier)
    {
        $this->load();
        return parent::runService($serviceIdentifier);
    }

    private function load()
    {
        if (!self::$isLoaded) {
            $language = \eZLocale::currentLocaleCode();
            $this->getHelper()->setSetting('language', $language);
            $userId = (int)$this->getHelper()->getParameter('user');
            $this->user = eZUser::fetch($userId);
            if (!$this->user instanceof eZUser) {
                throw new Exception('User not found');
            }
            $this->repository = OpenPaSensorRepository::instance();
            if ($this->user->contentObject()->attribute('class_identifier') != $this->repository->getOperatorContentClass()->attribute('identifier')) {
                throw new Exception('User is not an operator');
            }
            $this->sensorUser = $this->repository->getUserService()->loadFromEzUser($this->user);

            $this->availableGroups = $this->repository->getMembersAvailableGroups();
            $userGroups = $this->user->groups();
            foreach ($userGroups as $userGroup){
                if (isset($this->availableGroups[$userGroup])){
                    $this->groups[] = (int)$userGroup;
                }
            }

            self::$isLoaded = true;
        }
    }

    protected function getSchema()
    {
        $schema = [
            "title" => $this->user->contentObject()->attribute('name'),
            "type" => "object",
            'properties' => [
                'block_mode' => ['type' => 'boolean'],
                'sensor_deny_comment' => ['type' => 'boolean'],
                'sensor_can_behalf_of' => ['type' => 'boolean'],
                'moderate' => ['type' => 'boolean'],
                'restrict_mode' => ['type' => 'boolean'],
                'super_observer' => ['type' => 'boolean'],
                'user_list' => ['type' => 'boolean'],
            ],
        ];

        if (!empty($this->availableGroups)){
            $schema['properties']['groups'] = [
                'type' => 'object',
                'title' => 'Gruppi di utenti',
                'properties' => [
                    'user_groups' => [
                        'type' => 'array',
                        'enum' => array_keys($this->availableGroups),
                    ]
                ],
            ];
        }

        $schema['properties']['stats'] = [
            'type' => 'object',
            'title' => 'Accesso individuale alle statistiche',
            'properties' => [
                'stat' => [
                    'type' => 'array',
                    'enum' => array_keys($this->getStats()),
                ],
            ],
        ];

        return $schema;
    }

    protected function getOptions()
    {
        $options = [
            "form" => [
                "attributes" => [
                    "class" => 'opendata-connector',
                    "action" => $this->getHelper()->getServiceUrl('action', $this->getHelper()->getParameters()),
                    "method" => "post",
                    "enctype" => "multipart/form-data",
                ],
            ],
            'helper' => 'Username: ' . $this->user->Login . ' &middot; Email: ' . $this->user->Email,
            'hideInitValidationError' => true,
            'fields' => [
                'block_mode' => ['type' => 'checkbox', 'rightLabel' => 'Blocca utente'],
                'sensor_deny_comment' => ['type' => 'checkbox', 'rightLabel' => 'Impedisci all\'utente di commentare'],
                'sensor_can_behalf_of' => ['type' => 'checkbox', 'rightLabel' => 'Permetti all\'utente di inserire segnalazioni per conto di altri'],
                'user_list' => ['type' => 'checkbox', 'rightLabel' => 'Permetti all\'utente di accedere all\'elenco utenti'],
                'restrict_mode' => ['type' => 'checkbox', 'rightLabel' => 'Impedisci all\'utente di visualizzare le segnalazioni in cui non è coinvolto (incluse le statistiche)'],
                'super_observer' => ['type' => 'checkbox', 'rightLabel' => 'Consenti all\'utente di prendere in carico le segnalazioni in cui è coinvolto come osservatore'],
                'moderate' => ['type' => 'checkbox', 'rightLabel' => 'Modera sempre le attività dell\'utente'],
                'stats' => [
                    'fields' => [
                        'stat' => [
                            'hideNone' => true,
                            'multiple' => true,
                            'type' => 'checkbox',
                            'optionLabels' => array_values($this->getStats()),
                        ],
                    ],
                ],
            ],
        ];

        if (!empty($this->availableGroups)){
            $options['fields']['groups'] = [
                'fields' => [
                    'user_groups' => [
                        'hideNone' => true,
                        'multiple' => true,
                        'type' => 'checkbox',
                        'optionLabels' => array_column($this->availableGroups, 'name'),
                    ]
                ]
            ];
        }

        return $options;
    }

    protected function getView()
    {
        return [
            'parent' => 'bootstrap-edit',
            'locale' => 'it_IT',
        ];
    }

    protected function submit()
    {
        $data = $_POST;
        $blockMode = $data['block_mode'] === 'true';
        $denyComment = $data['sensor_deny_comment'] === 'true';
        $cabBehalf = $data['sensor_can_behalf_of'] === 'true';
        $moderate = $data['moderate'] === 'true';
        $restrictMode = $data['restrict_mode'] === 'true';
        $isSuperObserver = $data['super_observer'] === 'true';
        $stats = isset($data['stats']['stat']) ? $data['stats']['stat'] : [];
        $userList = $data['user_list'] === 'true';
        $assignGroups = isset($data['groups']['user_groups']) ? $data['groups']['user_groups'] : [];

        $removeGroups = [];
        $addGroups = [];
        if (!empty($this->availableGroups)){
            foreach ($this->groups as $group){
                if (!in_array($group, $assignGroups)){
                    $removeGroups[] = $group;
                }
            }
            foreach ($assignGroups as $group){
                if (!in_array($group, $this->groups) && isset($this->availableGroups[$group])){
                    $addGroups[] = $group;
                }
            }
        }

        $changeEnabling = $blockMode !== $this->sensorUser->isEnabled;
        $this->repository->getUserService()->setBlockMode($this->sensorUser, $blockMode);
        $this->repository->getUserService()->setCommentMode($this->sensorUser, !$denyComment);
        $this->repository->getUserService()->setModerationMode($this->sensorUser, $moderate);
        $this->repository->getUserService()->setBehalfOfMode($this->sensorUser, $cabBehalf);
        $this->repository->getUserService()->setRestrictMode($this->sensorUser, $restrictMode);
        $this->repository->getUserService()->setAsSuperObserver($this->sensorUser, $isSuperObserver);
        $this->removeGroups($removeGroups);
        $this->addGroups($addGroups);
        $this->grantStatData($this->sensorUser->id, $stats);

        if ($userList){
            $this->assignAccessToUserList($this->sensorUser->id);
        }else{
            $this->revokeAccessToUserList($this->sensorUser->id);
        }

        if ($changeEnabling) {
            TreeNode::clearCache($this->repository->getOperatorsRootNode()->attribute('node_id'));
            TreeNode::clearCache($this->repository->getGroupsRootNode()->attribute('node_id'));
        }
        $this->repository->getUserService()->refreshUser($this->sensorUser);
        $userSettings = $this->getData();
        $this->sensorUser = $this->repository->getUserService()->loadFromEzUser($this->user);
        $this->repository->getLogger()->info("Modified info for operator {$this->user->Login} #" . $this->user->id(), $userSettings);

        return [
            'message' => 'success',
            'method' => 'update',
            'content' => $userSettings,
        ];
    }

    protected function getData()
    {
        $data = [
            'block_mode' => $this->sensorUser->isEnabled === false,
            'sensor_deny_comment' => $this->sensorUser->commentMode === false,
            'sensor_can_behalf_of' => $this->sensorUser->behalfOfMode,
            'moderate' => $this->sensorUser->moderationMode,
            'restrict_mode' => $this->sensorUser->restrictMode,
            'super_observer' => $this->sensorUser->isSuperObserver,
            'user_list' => $this->hasAccessToUserList($this->sensorUser->id),
            'stats' => [
                'stat' => $this->getStatData($this->sensorUser->id),
            ],
        ];

        if (!empty($this->availableGroups)){
            $data['groups']['user_groups'] = $this->groups;
        }

        return $data;
    }

    protected function upload()
    {
        throw new InvalidArgumentException('Upload not handled');
    }

    private function getUserListRole()
    {
        $roleName = 'Sensor Read users';

        $role = eZRole::fetchByName($roleName);
        if (!$role instanceof eZRole) {
            $role = eZRole::create($roleName);
            $role->store();
            $role->appendPolicy('content', 'read', ['Class' => [eZContentClass::classIDByIdentifier('user')]]);
            $role->appendPolicy('sensor', 'user_list');
        }

        return $role;
    }

    public function assignAccessToUserList($userId)
    {
        $this->getUserListRole()->assignToUser((int)$userId);
    }

    public function revokeAccessToUserList($userId)
    {
        $this->getUserListRole()->removeUserAssignment((int)$userId);
    }

    public function hasAccessToUserList($userId)
    {
        $role = $this->getUserListRole();
        $userId = (int)$userId;
        $query = "SELECT count(*) FROM ezuser_role WHERE role_id='$role->ID' AND contentobject_id='$userId'";
        /** @var eZDBInterface $db */
        $db = eZDB::instance();
        $result = $db->arrayQuery($query);

        return $result[0]['count'] > 0;
    }
}
