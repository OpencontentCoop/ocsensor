<?php

class SensorStatisticAccess
{
    private static $instance;

    private $statisticsService;

    private $scopes;

    private $assignments;

    private $roles = [];

    private function __construct()
    {
        $translations = SensorTranslationHelper::instance();
        $this->statisticsService = OpenPaSensorRepository::instance()->getStatisticsService();
        $this->scopes = [
            'anonymous' => $translations->translate('Anonymous user'),
            'reporter' => $translations->translate('Authenticated user'),
            'operator' => $translations->translate('Operator'),
        ];
        $this->assignments = [
            'anonymous' => (int)eZINI::instance()->variable('UserSettings','AnonymousUserID'),
            'reporter' => (int)OpenPaSensorRepository::instance()->getUserRootNode()->attribute('contentobject_id'),
            'operator' => (int)OpenPaSensorRepository::instance()->getOperatorsRootNode()->attribute('contentobject_id'),
        ];
    }

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new SensorStatisticAccess();
        }

        return self::$instance;
    }

    /**
     * @param $scope
     * @param $statIdentifier
     * @param $allow
     * @return bool
     * @throws \Opencontent\Sensor\Api\Exception\NotFoundException
     * @throws Exception
     */
    public function setAccess($scope, $statIdentifier, $allow)
    {
        if (!isset($this->scopes[$scope])) {
            throw new Exception("Scope $scope not allowed");
        }

        $statisticIdentifier = $this->statisticsService->getStatisticFactoryByIdentifier($statIdentifier)->getIdentifier();
        $role = $this->getRole($scope);
        $currentList = $this->getCurrentAccessList($scope);
        if ($allow) {
            $currentList[] = $statisticIdentifier;
        } else {
            $currentList = array_diff($currentList, [$statisticIdentifier]);
        }

        $currentList = array_unique($currentList);
        $role->removePolicies();
        if (!empty($currentList)) {
            $role->appendPolicy('sensor', 'stat', ['ChartList' => $currentList]);
        }
        eZCache::clearByID(['user_info_cache']);

        return true;
    }

    private function getRole($scope)
    {
        if (!isset($this->roles[$scope])) {
            $roleName = 'Sensor Stat Access ' . ucfirst($scope);

            $role = eZRole::fetchByName($roleName);
            if (!$role instanceof eZRole) {
                $role = eZRole::create($roleName);
                $role->store();
            }

            if (isset($this->assignments[$scope]) && $this->assignments[$scope] > 0){
                $role->assignToUser($this->assignments[$scope]);
            }

            $this->roles[$scope] = $role;
        }

        return $this->roles[$scope];
    }

    private function getSingleStatRole($statisticIdentifier)
    {
        $roleName = 'Sensor Single Stat Access ' . $statisticIdentifier;

        $role = eZRole::fetchByName($roleName);
        if (!$role instanceof eZRole) {
            $role = eZRole::create($roleName);
            $role->store();
            $role->appendPolicy('sensor', 'stat', ['ChartList' => [$statisticIdentifier]]);
        }

        return $role;
    }

    public function assignAccessToStat($userId, $statisticIdentifier)
    {
        $this->getSingleStatRole($statisticIdentifier)->assignToUser((int)$userId);
    }

    public function revokeAccessToStat($userId, $statisticIdentifier)
    {
        $this->getSingleStatRole($statisticIdentifier)->removeUserAssignment((int)$userId);
    }

    public function hasAccessToStat($userId, $statisticIdentifier)
    {
        $role = $this->getSingleStatRole($statisticIdentifier);
        $userId = (int)$userId;
        $query = "SELECT count(*) FROM ezuser_role WHERE role_id='$role->ID' AND contentobject_id='$userId'";
        /** @var eZDBInterface $db */
        $db = eZDB::instance();
        $result = $db->arrayQuery($query);

        return $result[0]['count'] > 0;
    }

    public function getCurrentAccessHash()
    {
        $list = [];
        foreach (array_keys($this->getScopes()) as $scope){
            $list[$scope] = $this->getCurrentAccessList($scope);
        }

        return $list;
    }

    public function getCurrentAccessList($scope)
    {
        $role = $this->getRole($scope);

        $list = [];
        /** @var eZPolicy[] $policyList */
        $policyList = $role->policyList();
        foreach ($policyList as $policy) {
            if ($policy->attribute('function_name') == 'stat') {
                /** @var eZPolicyLimitation $policyLimitation */
                foreach ($policy->limitationList() as $policyLimitation) {
                    if ($policyLimitation->attribute('identifier') == 'ChartList') {
                        $list = array_merge($list, $policyLimitation->allValues());
                    }
                }
            }
        }

        return array_unique($list);
    }

    public function getScopes()
    {
        return $this->scopes;
    }
}
