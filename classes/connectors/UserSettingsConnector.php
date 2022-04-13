<?php

use Opencontent\Ocopendata\Forms\Connectors\AbstractBaseConnector;
use Opencontent\Sensor\Api\Values\User;

class UserSettingsConnector extends AbstractBaseConnector
{
    use CustomStatAccessTrait;
    use MemberGroupsTrait;

    private static $isLoaded;

    /**
     * @var eZUser
     */
    protected $user;

    /**
     * @var User
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
            if ($this->user->contentObject()->attribute('contentclass_id') != \eZINI::instance()->variable("UserSettings", "UserClassID")) {
                throw new Exception('Invalid user');
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

    protected function getData()
    {
        $data = [
            'block_mode' => $this->sensorUser->isEnabled === false,
            'sensor_deny_comment' => $this->sensorUser->commentMode === false,
            'sensor_can_behalf_of' => $this->sensorUser->behalfOfMode,
            'moderate' => $this->sensorUser->moderationMode,
            'restrict_mode' => $this->sensorUser->restrictMode,
        ];
        if (!empty($this->availableGroups)){
            $data['groups']['user_groups'] = $this->groups;
        }
        $data['stats'] = [
            'stat' => $this->getStatData($this->sensorUser->id),
        ];

        return $data;
    }

    protected function getSchema()
    {
        $schema = [
            'title' => $this->user->contentObject()->attribute('name'),
            'type' => 'object',
            'properties' => [
                'block_mode' => ['type' => 'boolean'],
                'sensor_deny_comment' => ['type' => 'boolean'],
                'sensor_can_behalf_of' => ['type' => 'boolean'],
                'moderate' => ['type' => 'boolean'],
                'restrict_mode' => ['type' => 'boolean'],
            ]
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
                    "enctype" => "multipart/form-data"
                ]
            ],
            'helper' => 'Username: ' . $this->user->Login . ' &middot; Email: ' . $this->user->Email,
            'hideInitValidationError' => true,
            'fields' => [
                'block_mode' => ['type' => 'checkbox', 'rightLabel' => 'Blocca utente'],
                'sensor_deny_comment' => ['type' => 'checkbox', 'rightLabel' => 'Impedisci all\'utente di commentare'],
                'sensor_can_behalf_of' => ['type' => 'checkbox', 'rightLabel' => 'Permetti all\'utente di inserire segnalazioni per conto di altri'],
                'moderate' => ['type' => 'checkbox', 'rightLabel' => 'Modera sempre le attività dell\'utente'],
                'restrict_mode' => ['type' => 'checkbox', 'rightLabel' => 'Impedisci all\'utente di visualizzare le segnalazioni in cui non è coinvolto (incluse le statistiche)'],
            ]
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

        $options['fields']['stats'] = [
            'fields' => [
                'stat' => [
                    'hideNone' => true,
                    'multiple' => true,
                    'type' => 'checkbox',
                    'optionLabels' => array_values($this->getStats()),
                ],
            ],
        ];

        return $options;
    }

    protected function getView()
    {
        return [
            'parent' => 'bootstrap-edit',
            'locale' => 'it_IT'
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
        $assignGroups = isset($data['groups']['user_groups']) ? $data['groups']['user_groups'] : [];
        $stats = isset($data['stats']['stat']) ? $data['stats']['stat'] : [];

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

        $this->repository->getUserService()->setBlockMode($this->sensorUser, $blockMode);
        $this->repository->getUserService()->setCommentMode($this->sensorUser, !$denyComment);
        $this->repository->getUserService()->setModerationMode($this->sensorUser, $moderate);
        $this->repository->getUserService()->setBehalfOfMode($this->sensorUser, $cabBehalf);
        $this->repository->getUserService()->setRestrictMode($this->sensorUser, $restrictMode);
        $this->removeGroups($removeGroups);
        $this->addGroups($addGroups);
        $this->grantStatData($this->sensorUser->id, $stats);

        $this->repository->getUserService()->refreshUser($this->sensorUser);
        $userSettings = $this->getData();
        $this->sensorUser = $this->repository->getUserService()->loadFromEzUser($this->user);
        $this->repository->getLogger()->info("Modified info for user {$this->user->Login} #" . $this->user->id(), $userSettings);

        return [
            'message' => 'success',
            'method' => 'update',
            'content' => $userSettings
        ];
    }

    protected function upload()
    {
        throw new InvalidArgumentException('Upload not handled');
    }
}
