<?php

use Opencontent\Sensor\Inefficiency\Listener;
use Opencontent\Sensor\Api\Values\Event;
use Opencontent\Sensor\Inefficiency\PostHandlerException;

class InefficiencyRetryHandler extends SQLIImportAbstractHandler implements ISQLIImportHandler
{
    const SENSOR_HANDLER_IDENTIFIER = 'inefficiency_retry';

    private $pendingActions = [];

    private $notes = [];

    public function initialize()
    {
        /** @var eZUser $user */
        $user = eZUser::fetchByName('admin');
        eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));
        $this->pendingActions = eZPendingActions::fetchObjectList(eZPendingActions::definition(), null, [
            'action' => Listener::PENDING_RETRY_ACTION,
        ], ['created' => 'asc']);
    }

    public function getProcessLength()
    {
        return count($this->pendingActions);
    }

    public function getNextRow()
    {
        return array_shift($this->pendingActions);
    }

    public function process($row)
    {
        if ($row instanceof eZPendingActions) {
            $event = SQLIImportUtils::safeUnserialize($row->attribute('param'));
            $handler = new Listener(OpenPaSensorRepository::instance());
            if ($event instanceof Event) {
                try {
                    $handler->handleSensorEvent($event);
                    $row->remove();
                }catch (PostHandlerException $e){
                    $note = '[ERROR] on pending action: ' . $row->attribute('id');
                    $row->setAttribute('action', $row->attribute('action') . '_error');
                    $row->store();
                    $this->notes[] = $note;
                }
            }
        }
    }

    public function cleanup()
    {
        // TODO: Implement cleanup() method.
    }

    public function getHandlerName()
    {
        return 'OpenSegnalazioni Inefficiency Batch Operation';
    }

    public function getHandlerIdentifier()
    {
        return self::SENSOR_HANDLER_IDENTIFIER;
    }

    public function getProgressionNotes()
    {
        return implode(PHP_EOL, $this->notes);
    }

}