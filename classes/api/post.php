<?php

class SensorApiPost
{
    protected $post = array();
    
    protected $data = array();

    public static function fromId( $id, $checkAccess = true )
    {
        $object = eZContentObject::fetch( $id );

        if ( !$object instanceof eZContentObject )
            throw new ezpContentNotFoundException( "Unable to find a post with ID $id" );

        if ( $checkAccess && !$object->attribute( 'can_read' ) )
            throw new ezpContentAccessDeniedException( $object->attribute( 'id' ) );

        return new SensorApiPost( $id );
    }

    protected function __construct( $id )
    {
        $this->post = SensorHelper::instanceFromContentObjectId( $id );
    }

    public function toHash()
    {
        foreach( $this->attributes() as $identifier )
            $this->attribute( $identifier );
        return $this->data;
    }

    public function attributes()
    {
        return array(
            "id",
            "url",
            "author",
            "created",
            "modified",
            "subject",
            "description",
            "geo",
            "internal_status",
            "image",
            "type",
            "state",
            "privacy",
            "moderation",
            "expiring_date",
            "resolution_time",
            "comment_count",
            "comment_unread_count",
            "message_count",
            "message_unread_count",
            "response_count",
            "response_unread_count"
        );
    }
    
    public function hasAttribute( $identifier )
    {
        return in_array( $identifier, $this->attributes() );
    }
    
    public function attribute( $identifier )
    {
        if ( !isset( $this->data[$identifier] ) )
        {
            if ( $identifier == 'id' )
                $this->data['id'] = (int)$this->post->attribute( 'id' );
            
            if ( $identifier == 'url' )
                $this->data['url'] = $this->post->attribute( 'post_url' );
    
            if ( $identifier == 'author' )
                $this->data['author'] = array(
                    'id' => $this->post->attribute( 'author_id' ),
                    'name' => $this->post->attribute( 'author_name' )
                );
    
            if ( $identifier == 'created' )
                $this->data['created'] = (int)$this->post->currentSensorPost->getContentObject()->attribute( 'published' );
            
            if ( $identifier == 'modified' )
                $this->data['modified'] = (int)$this->post->currentSensorPost->getContentObject()->attribute( 'modified' );
    
            if ( $identifier == 'subject' )
                $this->data['subject'] = $this->post->currentSensorPost->getContentObjectAttribute( 'subject' )->toString();
            
            if ( $identifier == 'description' )
                $this->data['description'] = $this->post->currentSensorPost->getContentObjectAttribute( 'description' )->toString();
            
            if ( $identifier == 'geo' )
                $this->data['geo'] = $this->post->currentSensorPost->getPostGeoArray();
            
            if ( $identifier == 'internal_status' )
                $this->data['internal_status'] = (int)$this->post->attribute( 'current_status' );
    
            if ( $identifier == 'image' )
            {
                $imageAttribute = $this->post->currentSensorPost->getContentObjectAttribute( 'image' );
                $this->data['image'] = $imageAttribute->hasContent() ? $imageAttribute->content()->attribute( 'original' ) : '';    
            }
            
            if ( $identifier == 'type' )
            {
                $type = $this->post->attribute( 'type' );
                $this->data['type'] = $type['identifier'];
            }
    
            if ( $identifier == 'state' )
            {
                $state = $this->post->attribute( 'current_object_state' );
                $this->data['state'] = $state['identifier'];
            }
    
            if ( $identifier == 'privacy' )
            {
                $state = $this->post->attribute( 'current_privacy_state' );
                $this->data['privacy'] = $state['identifier'];
            }
    
            if ( $identifier == 'moderation' )
            {
                $state = $this->post->attribute( 'current_moderation_state' );
                $this->data['moderation'] = $state['identifier'];
            }
    
            if ( $identifier == 'expiring_date' )
                $this->data['expiring_date'] = $this->post->attribute( 'expiring_date' );
            
            if ( $identifier == 'resolution_time' )
                $this->data['resolution_time'] = $this->post->attribute( 'resolution_time' );
    
            if ( $identifier == 'comment_count' )
                $this->data['comment_count'] = (int)$this->post->attribute( 'comment_count' );
            
            if ( $identifier == 'comment_unread_count' )
                $this->data['comment_unread_count'] = (int)$this->post->attribute( 'comment_unread_count' );
            
            if ( $identifier == 'message_count' )
                $this->data['message_count'] = (int)$this->post->attribute( 'message_count' );
            
            if ( $identifier == 'message_unread_count' )
                $this->data['message_unread_count'] = (int)$this->post->attribute( 'message_unread_count' );
            
            if ( $identifier == 'response_count' )
                $this->data['response_count'] = (int)$this->post->attribute( 'response_count' );
            
            if ( $identifier == 'response_unread_count' )
                $this->data['response_unread_count'] = (int)$this->post->attribute( 'response_unread_count' );
        }
        return $this->data[$identifier];
    }
}