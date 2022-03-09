<?php

class SensorBatchScenarioEditHandler extends SQLIImportAbstractHandler implements ISQLIImportHandler
{
    const SENSOR_HANDLER_IDENTIFIER = 'sensor_scenario_edit';

    private $idList;

    private $triggers;

    public function initialize()
    {
        $user = eZUser::fetchByName('admin');
        eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));
        $this->idList = explode('|', $this->options->attribute('id'));
        $this->triggers = $this->options->attribute('triggers');
    }

    public function getProcessLength()
    {
        return count($this->idList);
    }

    public function getNextRow()
    {
        $id = array_shift($this->idList);
        return $id;
    }

    public function process($row)
    {
        $object = eZContentObject::fetch((int)$row);
        if ($object instanceof eZContentObject && $object->attribute('class_identifier') == 'sensor_scenario'){
            $content = SQLIContent::fromContentObject($object);
            $content->fields->triggers = $this->triggers;
            SQLIContentPublisher::getInstance()->publish($content);
            unset($content);
        }
    }

    public function cleanup()
    {
        $cacheFile = 'scenarios.cache';
        $cacheFilePath = \eZDir::path(array(\eZSys::cacheDirectory(), 'ocopendata', 'sensor', 'scenarios', $cacheFile));
        \eZClusterFileHandler::instance($cacheFilePath)->delete();
    }

    public function getHandlerName()
    {
        return 'OpenSegnalazioni Batch Edit Scenario';
    }

    public function getHandlerIdentifier()
    {
        return self::SENSOR_HANDLER_IDENTIFIER;
    }

    public function getProgressionNotes()
    {
        return '';
    }

}
