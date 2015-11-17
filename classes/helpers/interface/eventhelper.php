<?php

interface SensorPostEventHelperInterface
{
    /**
     * @param string $eventName
     * @param array $eventDetails
     *
     * @return void
     */
    public function createEvent( $eventName, $eventDetails = array() );

    /**
     * @param eZNotificationEvent $event
     * @param array $parameters
     *
     * @return int eZNotificationEventHandler constant
     */
    public function handleEvent( eZNotificationEvent $event, array &$parameters );
}