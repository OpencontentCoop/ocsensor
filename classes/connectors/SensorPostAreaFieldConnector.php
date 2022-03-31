<?php

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;
use Opencontent\Sensor\Legacy\Utils\TreeNodeItem;

class SensorPostAreaFieldConnector extends FieldConnector
{
    private $areas = [];

    public function __construct($attribute, $class, $helper)
    {
        parent::__construct($attribute, $class, $helper);
        $tree = OpenPaSensorRepository::instance()->getAreasTree()->attribute('children');
        /** @var TreeNodeItem $area */
        foreach ($tree as $area) {
            $children = $area->attribute('children');
            if (count($children) > 0) {
                foreach ($children as $child) {
                    $this->areas['area-' . $child->attribute('id')] = $child->attribute('name');
                }
            } else {
                $this->areas['area-' . $area->attribute('id')] = $area->attribute('name');
            }
        }
    }

    public function getData()
    {
        $data = [];
        if ($rawContent = $this->getContent()) {
            foreach ($rawContent['content'] as $item) {
                $data[] = 'area-' . $item['id'];
            }
        }

        return $data;
    }

    public function getSchema()
    {
        return [
            "title" => $this->attribute->attribute('name'),
            'required' => (bool)$this->attribute->attribute('is_required'),
            'enum' => array_keys($this->areas),
        ];
    }

    public function getOptions()
    {
        return [
            "helper" => $this->attribute->attribute('description'),
            'label' => $this->attribute->attribute('name'),
            'type' => "select",
            'showMessages' => true,
            'hideNone' => true,
            'multiple' => false,
            'optionLabels' => array_values($this->areas),
        ];
    }

    public function setPayload($postData)
    {
        $data = array();
        $postData = (array)$postData;
        foreach ($postData as $item) {
            $data[] = (int)str_replace('area-', '', $item);
        }

        return empty( $data ) ? null : $data;
    }


}
