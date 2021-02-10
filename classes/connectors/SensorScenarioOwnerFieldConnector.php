<?php

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

class SensorScenarioOwnerFieldConnector extends FieldConnector
{
    private $operators;

    public function __construct($attribute, $class, $helper)
    {
        parent::__construct($attribute, $class, $helper);
        foreach (OpenPaSensorRepository::instance()->getOperatorsTree()->attribute('children') as $child) {
            $this->operators['id_' . $child->attribute('id')] = $child->attribute('name');
        }
    }

    public function getData()
    {
        $data = array();
        if ($rawContent = $this->getContent()) {
            foreach ($rawContent['content'] as $item) {
                $data[] = 'id_' . $item['id'];
            }
        }

        return $data;
    }

    public function getSchema()
    {
        $identifiers = array_keys($this->operators);
        $schema = array(
            "enum" => $identifiers,
            "title" => $this->attribute->attribute('name'),
            'required' => (bool)$this->attribute->attribute('is_required')
        );

        if ($schema['required']) {
            $schema['default'] = $identifiers[0];
        }

        return $schema;
    }

    public function getOptions()
    {
        return array(
            "label" => $this->attribute->attribute('name'),
            "helper" => $this->attribute->attribute('description'),
            "optionLabels" => array_values($this->operators),
            "hideNone" => (bool)$this->attribute->attribute('is_required'),
            "type" => "select",
            "multiple" => false,
        );
    }

    public function setPayload($postData)
    {
        $data = array();
        $postData = (array)$postData;
        foreach ($postData as $item) {
            $data[] = (int)str_replace('id_', '', $item);
        }

        return empty( $data ) ? null : $data;
    }
}