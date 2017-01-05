<?php

class SensorDigestType extends eZNotificationEventType
{
    const NOTIFICATION_TYPE_STRING = 'sensordigest';

    /*!
     Constructor
    */
    function __construct()
    {
        $this->eZNotificationEventType( self::NOTIFICATION_TYPE_STRING );
    }

    function initializeEvent( $event, $params )
    {
        eZDebugSetting::writeDebug( 'kernel-notification', $params, 'params for type' );
        $event->setAttribute( 'data_text1', $params['type'] );
        //$event->setAttribute( 'data_int2', $params['version'] );
    }

    function eventContent( $event )
    {
        // Todo
        return '';
    }
}

eZNotificationEventType::register( SensorDigestType::NOTIFICATION_TYPE_STRING, 'SensorDigestType' );