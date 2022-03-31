<?php

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;
use Opencontent\Sensor\Legacy\Utils\TreeNodeItem;

class SensorPostCategoryFieldConnector extends FieldConnector
{
    public function __construct($attribute, $class, $helper)
    {
        parent::__construct($attribute, $class, $helper);

    }

    public function getData()
    {
        $data = [];
        if ($rawContent = $this->getContent()) {
            foreach ($rawContent['content'] as $item) {
                $data[] = (int)$item['id'];
            }
        }

        return $data;
    }

    public function getSchema()
    {
        return [
            "title" => $this->attribute->attribute('name'),
            'required' => (bool)$this->attribute->attribute('is_required'),
        ];
    }

    public function getOptions()
    {
        return [
            "helper" => $this->attribute->attribute('description'),
            'label' => $this->attribute->attribute('name'),
            'type' => "tree",
            'showMessages' => false,
            'tree' => [
                'property_value' => 'id',
                'core' => [
                    'data' => $this->getCategoryTree(),
                    'multiple' => false,
                    'themes' => [
                        'variant' => 'large',
//                        'responsive' => true,
                    ],
                ],
                'plugins' => array('search'),
                'i18n' => [
                    'search' => SensorTranslationHelper::instance()->translate('Search'),
                ]
            ],
        ];
    }

    private function getCategoryTree()
    {
        $current = $this->getData();
        $data = [];
        $tree = OpenPaSensorRepository::instance()->getCategoriesTree()->attribute('children');
        foreach ($tree as $category) {

            $isItemSelected = in_array($category->attribute('id'), $current);
            $item = [
                'text' => $category->attribute('name'),
                'id' => (int)$category->attribute('id'),
                'state' => [
                    'opened' => $isItemSelected,
                    'disabled' => false,
                    'selected' => $isItemSelected
                ],
            ];

            $children = $category->attribute('children');
            if (count($children) > 0) {
                $childrenItems = [];
                $openParent = false;
                foreach ($children as $child) {
                    $isChildSelected = in_array($child->attribute('id'), $current);
                    if ($isChildSelected && !$openParent){
                        $openParent = true;
                    }
                    $childrenItems[] = [
                        'text' => $child->attribute('name'),
                        'id' => (int)$child->attribute('id'),
                        'state' => [
                            'opened' => $isChildSelected,
                            'disabled' => false,
                            'selected' => $isChildSelected,
                        ],
                    ];
                }
                $item['state']['disabled'] = true;
                if ($openParent){
                    $item['state']['opened'] = true;
                }
                $item['children'] = $childrenItems;
            }

            $data[] = $item;
        }

        return $data;
    }

    public function setPayload($postData)
    {
        $data = array();
        $postData = (array)$postData;
        foreach ($postData as $item) {
            $data[] = (int)$item;
        }

        return empty( $data ) ? null : $data;
    }


}
