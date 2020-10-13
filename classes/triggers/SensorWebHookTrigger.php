<?php

use Opencontent\Sensor\Api\Values\Participant as ParticipantAlias;

class SensorWebHookTrigger implements OCWebHookTriggerInterface
{
    protected static $schema;
    protected $identifier;
    protected $name;
    protected $repository;

    public function __construct($identifier, $name)
    {
        $this->identifier = $identifier;
        $this->name = $name;
        $this->repository = OpenPaSensorRepository::instance();
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getDescription()
    {
        return "Viene scatenato quando accade un evento di tipo '" . $this->getName() . "'. Il payload è un oggetto json API Sensor Post";
    }

    public function getName()
    {
        return $this->name;
    }

    public function canBeEnabled()
    {
        return true;
    }

    public function useFilter()
    {
        if ($this->identifier === 'on_create') {
            return false;
        }

        if (self::$schema === null) {
            /** @var \Opencontent\Sensor\Legacy\Utils\TreeNodeItem $categories */
            $categories = $this->repository->getCategoriesTree();
            $categoryList = [];
            foreach ($categories->attribute('children') as $category) {
                $categoryList['cat-' . $category->attribute('id')] = '<strong>' . addcslashes($category->attribute('name'), "'") . '</strong>';
                foreach ($category->attribute('children') as $child) {
                    $categoryList['cat-' . $child->attribute('id')] = str_replace("'", "&apos;", $child->attribute('name'));
                }
            }

            $groups = $this->repository->getGroupsTree();
            $groupList = [];
            foreach ($groups->attribute('children') as $group) {
                $groupList['group-' . $group->attribute('id')] = str_replace("'", "&apos;", $group->attribute('name'));
            }

            $schema = [
                'schema' => [
                    'title' => "Il webhook viene eseguito quando si verificano tutte le condizioni di seguito indicate",
                    'type' => 'object',
                    'properties' => [
                        'group' => [
                            'title' => "1 - Seleziona un gruppo",
                            'type' => 'string',
                            'enum' => array_keys($groupList),
                        ],
                        'category' => [
                            'title' => "2 - Seleziona una o più categorie",
                            'type' => 'string',
                            'enum' => array_keys($categoryList),
                        ],
                    ],
                ],
                'options' => [
                    'fields' => [
                        'category' => [
                            'helper' => "Il webhook viene eseguito solo se la segnalazione rientra nelle categorie selezionate",
                            'optionLabels' => array_values($categoryList),
                            'multiple' => true,
                            'type' => 'checkbox',
                            'sort' => false,
                        ],
                        'group' => [
                            'helper' => "Il webhook viene eseguito solo se la segnalazione è assegnata a un gruppo selezionato",
                            'optionLabels' => array_values($groupList),
                            'type' => 'select',
                        ],
                    ],
                ]
            ];

            self::$schema = json_encode($schema);
        }

        return self::$schema;
    }

    /* 
     * Code to test:
     * $repository = OpenPaSensorRepository::instance();
     * $post = $repository->getPostService()->loadPost(124);
     * $siteUrl = '/';
     * eZURI::transformURI($siteUrl,true, 'full');
     * $endpointUrl = '/api/sensor';
     * eZURI::transformURI($endpointUrl, true, 'full');
     * $openApiTools = new \Opencontent\Sensor\OpenApi(
     *     $repository,
     *     $siteUrl,
     *     $endpointUrl
     * );
     * $postSerializer = new \Opencontent\Sensor\OpenApi\PostSerializer($openApiTools);
     * OCWebHookEmitter::emit('on_assign', $postSerializer->serialize($post), OCWebHookQueue::HANDLER_IMMEDIATE);
    */
    public function isValidPayload($payload, $filters)
    {
        if ($this->identifier === 'on_create' || empty($filters)) {
            return true;
        }

        $filters = json_decode($filters, true);
        $categoryIdList = $groupIdList = [];
        if (isset($filters['category'])) {
            $categoryIdList = explode(',', $filters['category']);
        }
        if (isset($filters['group'])) {
            $groupIdList = explode(',', $filters['group']);
        }

        if (empty($categoryIdList) && empty($groupIdList)) {
            return true;
        }

        try {
            $post = $this->repository->getPostService()->loadPost($payload['id']);
            $filteredByCategory = false;
            if (!empty($categoryIdList)) {
                foreach ($post->categories as $category) {
                    if (in_array('cat-' . $category->id, $categoryIdList)) {
                        $filteredByCategory = true;
                        break;
                    }
                }
            } else {
                $filteredByCategory = true;
            }

            $filteredByGroup = false;
            if (!empty($groupIdList)) {
                foreach ($post->owners->getParticipantIdListByType(ParticipantAlias::TYPE_GROUP) as $groupId) {
                    if (in_array('group-' . $groupId, $groupIdList)) {
                        $filteredByGroup = true;
                        break;
                    }
                }
            } else {
                $filteredByGroup = true;
            }

            return $filteredByCategory && $filteredByGroup;

        } catch (Exception $e) {
            eZDebug::writeError($e->getMessage(), __METHOD__);
        }

        return false;
    }
}
