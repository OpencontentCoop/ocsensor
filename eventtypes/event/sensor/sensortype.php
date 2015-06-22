<?php

class SensorType extends eZWorkflowEventType
{

    const WORKFLOW_TYPE_STRING = 'sensor';

    function __construct()
    {
        $this->eZWorkflowEventType(
            self::WORKFLOW_TYPE_STRING,
            ezpI18n::tr( 'sensor/workflow/event', 'Workflow Sensor' )
        );
    }

    /**
     * @param eZWorkflowProcess $process
     * @param eZEvent $event
     *
     * @return int
     */
    function execute( $process, $event )
    {
        $parameters = $process->attribute( 'parameter_list' );

        try
        {
            SensorHelper::executeWorkflow( $parameters, $process, $event );
            return eZWorkflowType::STATUS_ACCEPTED;
        }
        catch( Exception $e )
        {
            eZDebug::writeError( $e->getMessage(), __METHOD__ );
            return eZWorkflowType::STATUS_REJECTED;
        }

    }
}

eZWorkflowEventType::registerEventType( SensorType::WORKFLOW_TYPE_STRING, 'SensorType' );
