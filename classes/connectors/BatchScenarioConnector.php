<?php

use Opencontent\Ocopendata\Forms\Connectors\AbstractBaseConnector;
use Opencontent\Sensor\Legacy\Scenarios\SensorScenario;

class BatchScenarioConnector extends AbstractBaseConnector
{
    private $events = [];

    private $triggersAttribute = [];

    public function __construct($identifier)
    {
        parent::__construct($identifier);
        $this->events = SensorScenario::getAvailableEvents();
        $scenarioClass = eZContentClass::fetchByIdentifier('sensor_scenario');
        if ($scenarioClass instanceof eZContentClass) {
            $dataMap = $scenarioClass->dataMap();
            if (isset($dataMap['triggers'])) {
                $this->triggersAttribute = $dataMap['triggers'];
            } else {
                throw new Exception("Class attribute sensor_scenario/triggers not found");
            }
        } else {
            throw new Exception("Class sensor_scenario not found");
        }
    }

    protected function getData()
    {
        return null;
    }

    protected function getSchema()
    {
        if (!$this->getHelper()->hasParameter('query')) {
            throw new Exception("Missing query");
        }

        return [
            "title" => "Modifica massiva automazioni",
            "type" => "object",
            "properties" => [
                "triggers" => [
                    'enum' => array_keys($this->events),
                    'type' => 'array',
                    'title' => $this->triggersAttribute->attribute('name'),
                ],
            ],
        ];
    }

    protected function getOptions()
    {
        return [
            "form" => [
                "attributes" => [
                    "action" => $this->getHelper()->getServiceUrl('action', $this->getHelper()->getParameters()),
                    "method" => "post",
                ],
                "buttons" => [
                    "submit" => [],
                ],
            ],
            "fields" => [
                "triggers" => [
                    "label" => $this->triggersAttribute->attribute('name'),
                    "helper" => $this->triggersAttribute->attribute('description'),
                    "optionLabels" => array_values($this->events),
                    "type" => "checkbox",
                    "multiple" => true,
                ],
            ],
        ];
    }

    protected function getView()
    {
        return [
            "parent" => "bootstrap-edit",
            "locale" => "it_IT",
        ];
    }

    protected function submit()
    {
        if (SensorBatchOperations::instance()->hasActiveOperation(SensorBatchScenarioEditHandler::SENSOR_HANDLER_IDENTIFIER)){
            throw new Exception("Una modifica massiva alle automazioni è già presente a sistema: riprova più tardi");
        }

        $triggers = $_POST['triggers'];
        $query = $this->getHelper()->getParameter('query');
        $searchRepository = new \Opencontent\Opendata\Api\ContentSearch();
        $searchRepository->setCurrentEnvironmentSettings(new FullEnvironmentSettings());
        $data = [];
        while ($query) {
            $results = $searchRepository->search($query);
            $query = $results->nextPageQuery;
            foreach ($results->searchHits as $hit) {
                $data[] = $hit['metadata']['id'];
            }
        }

        SensorBatchOperations::instance()->addPendingOperation(SensorBatchScenarioEditHandler::SENSOR_HANDLER_IDENTIFIER, [
            'id' => implode('|', $data),
            'triggers' => implode('|', $triggers),
        ])->run();

        return true;
    }

    protected function upload()
    {
        throw new InvalidArgumentException('Upload not handled');
    }
}
