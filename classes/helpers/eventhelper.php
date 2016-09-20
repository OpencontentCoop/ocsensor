<?php

class SensorPostEventHelper implements SensorPostEventHelperInterface
{
    const EVENT_CREATOR_FIELD = 'data_int2';

    const EVENT_TIMESTAMP_FIELD = 'data_int3';

    const EVENT_DETAILS_FIELD = 'data_text2';

    /**
     * @var SensorPost
     */
    protected $post;

    /**
     * @var SensorNotificationHelper
     */
    protected $notificationHelper;

    public $availableEvents = array(
        'on_create',
        'on_update',
        'on_assign',
        'on_fix',
        'on_force_fix',
        'on_close',
        'on_make_private',
        'on_make_public',
        'on_moderate',
        'on_add_observer',
        'on_add_category',
        'on_add_area',
        'on_set_expiry',
        'on_add_comment',
        'on_reopen',
        'on_add_message',
        'on_add_response',
        'on_edit_comment',
        'on_edit_message',
        'on_restore',
        'on_remove'
    );

    protected function __construct( SensorPost $post )
    {
        $this->post = $post;
        $this->notificationHelper = SensorNotificationHelper::instance( $this->post );
    }

    final public static function instance( SensorPost $post )
    {
        $className = false;
        if ( eZINI::instance( 'ocsensor.ini' )->hasVariable( 'PHPCLasses', 'EventHelper' ) )
        {
            $className = eZINI::instance( 'ocsensor.ini' )->variable( 'PHPCLasses', 'EventHelper' );
        }
        if ( $className && class_exists( $className ) )
        {            
            return new $className( $post );
        }
        return new SensorPostEventHelper( $post );
    }

    public function createEvent( $eventName, $eventDetails = array() )
    {
        foreach( $this->notificationHelper->postNotificationTypes() as $type )
        {
            if ( $type['identifier'] == $eventName )
            {
                $event = $this->post->getCollaborationItem()->createNotificationEvent( $eventName );
                $event->setAttribute( self::EVENT_CREATOR_FIELD, eZUser::currentUserID() );
                $event->setAttribute( self::EVENT_TIMESTAMP_FIELD, time() );
                $event->setAttribute( self::EVENT_DETAILS_FIELD, json_encode( $eventDetails ) );
                $event->store();

                $parameters = array();
                $this->handleEvent( $event, $parameters );
            }
        }
    }

    public function handleEvent( eZNotificationEvent $event, array &$parameters )
    {
        return $this->notificationHelper->handleEvent( $event, $parameters );
    }
}
