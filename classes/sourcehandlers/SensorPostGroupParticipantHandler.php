<?php

class SensorPostGroupParticipantHandler extends SQLIImportAbstractHandler implements ISQLIImportHandler
{
    const SENSOR_HANDLER_IDENTIFIER = 'sensor_group_reindex';

    private $groups = [];

    public function initialize()
    {
        $user = eZUser::fetchByName('admin');
        eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));
        $this->groups = explode('-', $this->options->attribute('groups'));
    }

    public function getProcessLength()
    {
        return count($this->groups);
    }

    public function getNextRow()
    {
        $id = array_shift($this->groups);
        return $id;
    }

    public function process($row)
    {
        SensorReindexer::reindexPostsByGroupId((int)$row);
    }

    public function cleanup()
    {
        // TODO: Implement cleanup() method.
    }

    public function getHandlerName()
    {
        return 'OpenSegnalazioni Reindex Post Group Participant';
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
