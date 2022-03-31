<?php

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\SelectionField;

class SensorPostTypeFieldConnector extends SelectionField
{
    private $types = [];

    public function __construct($attribute, $class, $helper)
    {
        parent::__construct($attribute, $class, $helper);
        foreach (OpenPaSensorRepository::instance()->getPostTypeService()->loadPostTypes() as $type){
            $this->types[$type->identifier] = $type->name;
        }
    }

    public function getSchema()
    {
        $schema = array(
            "enum" => array_keys($this->types),
            "title" => $this->attribute->attribute('name'),
            'required' => (bool)$this->attribute->attribute('is_required')
        );

        if ($schema['required']){
            $schema['default'] = current(array_keys($this->types));
        }

        return $schema;
    }

    public function getOptions()
    {
        $options = parent::getOptions();
        $options['optionLabels'] = array_values($this->types);
        $options['hideNone'] = true;

        return $options;
    }


}
