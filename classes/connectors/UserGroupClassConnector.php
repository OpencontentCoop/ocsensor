<?php

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\ClassConnector;

class UserGroupClassConnector extends ClassConnector
{
    use CustomStatAccessTrait;

    private $categories = [];

    public function __construct(eZContentClass $class, $helper)
    {
        parent::__construct($class, $helper);
        foreach (OpenPaSensorRepository::instance()->getCategoriesTree()->attribute('children') as $category){
            $this->categories[$category->attribute('node_id')] = $category->attribute('name');
        }
    }


    public function getData()
    {
        $data = parent::getData();
        $list = [];
        if ($this->isUpdate()){
            $group = eZContentObject::fetch((int)$this->getHelper()->getParameter('object'));
            if ($group instanceof eZContentObject){
                $role = $this->getGroupRole($group);
                /** @var eZPolicy[] $policyList */
                $policyList = $role->policyList();
                foreach ($policyList as $policy) {
                    if ($policy->attribute('function_name') == 'category_access') {
                        /** @var eZPolicyLimitation $policyLimitation */
                        foreach ($policy->limitationList() as $policyLimitation) {
                            if ($policyLimitation->attribute('identifier') == 'Node') {
                                $list = array_merge($list, $policyLimitation->allValues());
                            }
                        }
                    }
                }
            }
        }
        $data['allow_categories']['categories'] = $list;
        $data['stats'] = [
            'stat' => $this->getStatData($this->getHelper()->getParameter('object')),
        ];

        return $data;
    }

    public function getSchema()
    {
        $schema = parent::getSchema();
        $schema['properties']['allow_categories'] = [
            'title' => 'Abilita la selezione delle macro categorie (e eventuali relative categorie) in inserimento della segnalazione:',
            "type" => "object",
            "properties" => [
                'categories' => [
                    'type' => 'array',
                    'enum' => array_keys($this->categories),
                ]
            ]
        ];
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

    public function getOptions()
    {
        $options = parent::getOptions();
        $options['fields']['allow_categories'] = [
            "fields" => [
                'categories' => [
                    'hideNone' => true,
                    'multiple' => true,
                    'type' => 'checkbox',
                    'optionLabels' => array_values($this->categories),
                ]
            ]
        ];
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

    public function submit()
    {
        $data = $this->getSubmitData();
        $submit = parent::submit();
        $group = eZContentObject::fetch((int)$submit['content']['metadata']['id']);
        if ($group instanceof eZContentObject){
            $categories = isset($data['allow_categories']['categories']) ? $data['allow_categories']['categories'] : [];
            $this->updateGroupRole($group, $categories);

            $stats = isset($data['stats']['stat']) ? $data['stats']['stat'] : [];
            $this->grantStatData($group->attribute('id'), $stats);
        }
        return $submit;
    }

    private function getGroupRole(eZContentObject $group)
    {
        $roleName = self::generateGroupRoleName($group->attribute('id'));
        $role = eZRole::fetchByName($roleName);
        if (!$role instanceof eZRole) {
            $role = eZRole::create($roleName);
            $role->store();
            $role->assignToUser($group->attribute('id'));
        }

        return $role;
    }

    private function updateGroupRole(eZContentObject $group, array $categoryNodeList)
    {
        $role = $this->getGroupRole($group);
        $role->removePolicies();
        if (!empty($categoryNodeList)){
            $role->appendPolicy('sensor', 'category_access', ['Node' => $categoryNodeList]);
        }

        eZCache::clearByID(['user_info_cache']);
    }

    public static function generateGroupRoleName($id)
    {
        return 'Sensor Category Access #' . $id;
    }
}
