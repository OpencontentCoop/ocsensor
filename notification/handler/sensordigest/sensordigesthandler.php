<?php

class SensorDigestHandler extends eZNotificationEventHandler
{
    const NOTIFICATION_HANDLER_ID = 'sensordigest';
    const NOTIFICATION_HANDLER_RULE_PREFIX = 'openpaiter_' . self::NOTIFICATION_HANDLER_ID;

    /**
     * SensorDigestHandler constructor.
     */
    function __construct()
    {
        $this->eZNotificationEventHandler( self::NOTIFICATION_HANDLER_ID, "Sensor Digest Handler" );

    }

    /**
     * @return array
     */
    function attributes()
    {
        return array_merge( array( 'collaboration_handlers',
            'collaboration_selections' ),
            eZNotificationEventHandler::attributes() );
    }

    /**
     * @param $attr
     * @return bool
     */
    function hasAttribute( $attr )
    {
        return in_array( $attr, $this->attributes() );
    }

    /**
     * @param $attr
     * @return array|bool|mixed|null
     */
    function attribute( $attr )
    {
        if ( $attr == 'collaboration_handlers' )
        {
            return $this->collaborationHandlers();
        }
        else if ( $attr == 'collaboration_selections' )
        {
            $selections = $this->collaborationSelections();
            return $selections;
        }
        return eZNotificationEventHandler::attribute( $attr );
    }

    /*!
     Returns the available collaboration handlers.
    */
    function collaborationHandlers()
    {
        return eZCollaborationItemHandler::fetchList();
    }

    function collaborationSelections()
    {
        $rules = eZCollaborationNotificationRule::fetchList();
        $selection = array();
        foreach( $rules as $rule )
        {
            if (strpos( $rule->attribute( 'collab_identifier' ), self::NOTIFICATION_HANDLER_RULE_PREFIX) !== false )
            {
                $selection[] = $rule->attribute( 'collab_identifier' );
            }
        }
        return $selection;
    }

    /**
     * @param eZNotificationEvent $event
     *
     * @return bool
     */
    function handle( $event )
    {
        eZDebugSetting::writeDebug( 'kernel-notification', $event, "trying to handle event" );
        if ( $event->attribute( 'event_type_string' ) == SensorDigestType::NOTIFICATION_TYPE_STRING )
        {
            eZLog::write(print_r($event, 1), 'sensorgigest.log');
        }
        return true;
    }

    function fetchHttpInput( $http, $module )
    {
        if ( $http->hasPostVariable( 'CollaborationHandlerSelection'  ) )
        {
            $oldSelection = $this->collaborationSelections();
            $selection = array();
            if ( $http->hasPostVariable( 'CollaborationHandlerSelection_' . self::NOTIFICATION_HANDLER_ID  ) )
                $selection = $http->postVariable( 'CollaborationHandlerSelection_' . self::NOTIFICATION_HANDLER_ID );
            $createRules = array_diff( $selection, $oldSelection );
            $removeRules = array_diff( $oldSelection, $selection );
            if ( count( $removeRules ) > 0 )
                eZCollaborationNotificationRule::removeByIdentifier( array( $removeRules ) );
            foreach ( $createRules as $createRule )
            {
                $rule = eZCollaborationNotificationRule::create( $createRule );
                $rule->store();
            }
        }
    }
}