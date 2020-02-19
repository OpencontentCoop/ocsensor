<?php

class SensorWebHookTrigger implements OCWebHookTriggerInterface
{
    protected $identifier;

    protected $name;

    protected $repository;

    protected static $schema;

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

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return "Viene scatenato quando accade un evento di tipo '" . $this->getName() . "'. Il payload è un oggetto json API Sensor Post";
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
            $list = [];
            foreach ($categories->attribute('children') as $category) {
                $list[$category->attribute('id')] = '<strong>' . $category->attribute('name') . '</strong>';
                foreach ($category->attribute('children') as $child) {
                    $list[$child->attribute('id')] = $child->attribute('name');
                }
            }

            $schema = [
                'schema' => [
                    'title' => "Seleziona una o più categorie",
                    'type' => 'object',
                    'properties' => [
                        'category' => [
                            'type' => 'string',
                            'enum' => array_keys($list),
                        ],
                    ],
                ],
                'options' => [
                    'helper' => "Il webhook viene eseguito solo se la segnalazione rientra nelle categorie selezionate",
                    'fields' => [
                        'category' => [
                            'name' => 'category',
                            'optionLabels' => array_values($list),
                            'multiple' => true,
                            'type' => 'checkbox',
                            'sort' => false,
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
        $categoryIdList = explode(',', $filters['category']);

        try {
            $post = $this->repository->getPostService()->loadPost($payload['id']);
            foreach ($post->categories as $category) {
                if (in_array($category->id, $categoryIdList)) {
                    return true;
                }
            }
        } catch (Exception $e) {
            eZDebug::writeError($e->getMessage(), __METHOD__);
        }

        return false;
    }
}
