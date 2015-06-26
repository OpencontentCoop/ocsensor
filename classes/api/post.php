<?php

class SensorApiPost implements ArrayAccess, Iterator
{
    protected $data = array();

    protected $iteratorPointer = null;

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
        $post = SensorHelper::instanceFromContentObjectId( $id );

        $this->data['id'] = (int)$post->attribute( 'id' );
        $this->data['url'] = $post->attribute( 'post_url' );

        $this->data['author'] = array(
            'id' => $post->attribute( 'author_id' ),
            'name' => $post->attribute( 'author_name' )
        );

        $this->data['created'] = (int)$post->currentSensorPost->objectHelper->getContentObject()->attribute( 'published' );
        $this->data['modified'] = (int)$post->currentSensorPost->objectHelper->getContentObject()->attribute( 'modified' );

        $this->data['subject'] = $post->currentSensorPost->objectHelper->getContentObjectAttribute( 'subject' )->toString();
        $this->data['description'] = $post->currentSensorPost->objectHelper->getContentObjectAttribute( 'description' )->toString();
        $this->data['geo'] = $post->currentSensorPost->objectHelper->getPostGeoArray();
        $this->data['internal_status'] = (int)$post->attribute( 'current_status' );

        $imageAttribute = $post->currentSensorPost->objectHelper->getContentObjectAttribute( 'image' );
        $this->data['image'] = $imageAttribute->hasContent() ? $imageAttribute->content()->attribute( 'original' ) : '';

        $type = $post->attribute( 'type' );
        $this->data['type'] = $type['identifier'];

        $state = $post->attribute( 'current_object_state' );
        $this->data['state'] = $state['identifier'];

        $state = $post->attribute( 'current_privacy_state' );
        $this->data['privacy'] = $state['identifier'];

        $state = $post->attribute( 'current_moderation_state' );
        $this->data['moderation'] = $state['identifier'];

        $this->data['expiring_date'] = $post->attribute( 'expiring_date' );
        $this->data['resolution_time'] = $post->attribute( 'resolution_time' );

        $this->data['comment_count'] = (int)$post->attribute( 'comment_count' );
        $this->data['comment_unread_count'] = (int)$post->attribute( 'comment_unread_count' );
        $this->data['message_count'] = (int)$post->attribute( 'message_count' );
        $this->data['message_unread_count'] = (int)$post->attribute( 'message_unread_count' );
        $this->data['response_count'] = (int)$post->attribute( 'response_count' );
        $this->data['response_unread_count'] = (int)$post->attribute( 'response_unread_count' );

        $this->iteratorPointer = array_keys( $this->data );
    }

    public function toHash()
    {
        return $this->data;
    }

    public function current()
    {
        return $this->data[current( $this->iteratorPointer )];
    }

    public function next()
    {
        next( $this->iteratorPointer );
    }

    public function key()
    {
        return current( $this->iteratorPointer );
    }

    public function valid()
    {
        return isset( $this->data[current( $this->iteratorPointer )] );
    }

    public function rewind()
    {
        reset( $this->iteratorPointer );
    }

    public function offsetExists( $offset )
    {
        return isset( $this->data[$offset] );
    }

    public function offsetGet( $offset )
    {
        return $this->data[$offset];
    }

    public function offsetSet( $offset, $value )
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset( $offset )
    {
        unset( $this->data[$offset] );
    }
}