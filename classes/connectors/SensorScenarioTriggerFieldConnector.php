<?php

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;
use Opencontent\Sensor\Legacy\Scenarios\SensorScenario;

class SensorScenarioTriggerFieldConnector extends FieldConnector
{
    private $events;

    public function __construct($attribute, $class, $helper)
    {
        parent::__construct($attribute, $class, $helper);
        $this->events = SensorScenario::getAvailableEvents();
    }


    public function getData()
    {
        $rawContent = $this->getContent();
        if ($rawContent && !empty($rawContent['content'])){
            return explode('|', $rawContent['content'])[0];
        }
        return null;
    }

    public function getSchema()
    {
        $identifiers = array_keys($this->events);
        $schema = array(
            "enum" => $identifiers,
            "title" => $this->attribute->attribute('name'),
            'required' => (bool)$this->attribute->attribute('is_required')
        );

        if ($schema['required']){
            $schema['default'] = $identifiers[0];
        }

        return $schema;
    }

    public function getOptions()
    {
        return array(
            "label" => $this->attribute->attribute('name'),
            "helper" => $this->attribute->attribute('description'),
            "optionLabels" => array_values($this->events),
            "hideNone" => (bool)$this->attribute->attribute('is_required'),
            "type" => "select",
            "multiple" => false,
        );
    }
}