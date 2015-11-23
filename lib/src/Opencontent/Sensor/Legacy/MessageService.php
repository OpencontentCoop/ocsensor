<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 23/11/15
 * Time: 18:58
 */

namespace OpenContent\Sensor\Legacy;

use OpenContent\Sensor\Api\Values\Message;
use OpenContent\Sensor\Core\MessageService as MessageServiceBase;
use OpenContent\Sensor\Api\Values\Post;
use OpenContent\Sensor\Api\Exception\BaseException;
use eZPersistentObject;
use eZCollaborationItem;
use eZCollaborationSimpleMessage;
use eZCollaborationItemMessageLink;
use ezpI18n;
use eZUser;
use eZTemplate;

class MessageService extends MessageServiceBase
{

    const TIMELINE_ITEM = 0;

    const COMMENT = 1;

    /**
     * @var Message[]
     */
    protected $messagesByPost = array();

    /**
     * @var Repository
     */
    protected $repository;

    protected function loadMessagesByPost( Post $post )
    {
        $postInternalId = $post->internalId;
        if ( !isset( $this->messagesByPost[$postInternalId] ) )
        {
            $this->messagesByPost[$postInternalId] = array(
                'comment' => array(),
                'timeline' => array(),
                'private' => array()
            );

            /** @var eZCollaborationItemMessageLink[] $messageLinks */
            $messageLinks = eZPersistentObject::fetchObjectList(
                eZCollaborationItemMessageLink::definition(),
                null,
                array(
                    'collaboration_id' => $postInternalId,
                ),
                array( 'created' => 'asc' ),
                null,
                true
            );

            $simpleMessageIdList = array();
            foreach ( $messageLinks as $messageLink )
            {
                $simpleMessageIdList[] = $messageLink->attribute( 'message_id' );
            }

            /** @var eZCollaborationSimpleMessage[] $simpleMessages */
            $simpleMessages = eZPersistentObject::fetchObjectList(
                eZCollaborationSimpleMessage::definition(),
                null,
                array(
                    'id' => array( $simpleMessageIdList ),
                ),
                array( 'created' => 'asc' ),
                null,
                true
            );

            $messageData = array();
            foreach( $simpleMessages as $simpleMessage )
            {
                $messageItem = array(
                    'message' => $simpleMessage,
                    'links' => array()
                );
                foreach( $messageLinks as $messageLink )
                {
                    if ( $messageLink->attribute( 'message_id' ) == $simpleMessage->attribute( 'id' ) )
                    {
                        $messageItem['links'][] = $messageLink;
                    }
                }
                $messageData[] = $messageItem;
            }

            foreach( $messageData as $messageItem )
            {
                if ( count( $messageItem['links'] ) > 0 )
                {
                    /** @var eZCollaborationSimpleMessage $simpleMessage */
                    $simpleMessage = $messageItem['message'];

                    /** @var eZCollaborationItemMessageLink $firstLink */
                    $firstLink = $messageItem['links'][0];

                    if ( $firstLink->attribute( 'message_type' ) == self::COMMENT )
                    {
                        $message = new Message\Comment();
                        $type = 'comment';
                        $message->text = $simpleMessage->attribute( 'data_text1' );
                    }
                    elseif ( $firstLink->attribute( 'message_type' ) == self::TIMELINE_ITEM )
                    {
                        $message = new Message\TimelineItem();
                        $type = 'timeline';
                        $message->text = $this->getTimelineItemText( $simpleMessage );
                    }
                    else
                    {
                        $message = new Message\PrivateMessage();
                        $type = 'private';
                        $message->text = $simpleMessage->attribute( 'data_text1' );

                        $message->receivers = array( $firstLink->attribute( 'participant_id' ) );
                        /** @var eZCollaborationItemMessageLink $link */
                        foreach( $messageItem['links'] as $link )
                        {
                            //@todo
                            $message->receivers[] = $link->attribute( 'participant_id' );
                        }
                    }
                    $message->id = $simpleMessage->attribute( 'id' );
                    $message->creator = $simpleMessage->attribute( 'creator_id' );
                    $message->published = Utils::getDateTimeFromTimestamp( $simpleMessage->attribute( 'created' ) );
                    $message->modified = Utils::getDateTimeFromTimestamp( $simpleMessage->attribute( 'modified' ) );

                    $this->messagesByPost[$postInternalId][$type][] = $message;
                }
            }
        }

        return $this->messagesByPost[$postInternalId];
    }

    public function loadCommentCollectionByPost( Post $post )
    {
        $allMessages = $this->loadMessagesByPost( $post );
        /** @var Message[] $messages */
        $messages = $allMessages['comment'];

        $collection = new Message\CommentCollection();
        $collection->count = count( $messages );
        $collection->messages = $messages;
        $collection->lastMessage = array_pop( $messages );
        return $collection;
    }

    public function loadPrivateMessageCollectionByPost( Post $post )
    {
        $allMessages = $this->loadMessagesByPost( $post );
        /** @var Message[] $messages */
        $messages = $allMessages['private'];

        $collection = new Message\CommentCollection();
        $collection->count = count( $messages );
        $collection->messages = $messages;
        $collection->lastMessage = array_pop( $messages );
        return $collection;
    }

    public function loadTimelineItemCollectionByPost( Post $post )
    {
        $allMessages = $this->loadMessagesByPost( $post );
        /** @var Message[] $messages */
        $messages = $allMessages['timeline'];

        $collection = new Message\CommentCollection();
        $collection->count = count( $messages );
        $collection->messages = $messages;
        $collection->lastMessage = array_pop( $messages );
        return $collection;
    }

    protected function getTimelineItemText( eZCollaborationSimpleMessage $simpleMessage )
    {
        $result = '';
        if ( $simpleMessage instanceof eZCollaborationSimpleMessage )
        {
            $text = $simpleMessage->attribute( 'data_text1' );
            $parts = explode( ' by ', $text );
            if ( !isset( $parts[1] ) )
            {
                $parts = explode( ' to ', $text );
            }
            if ( isset( $parts[1] ) )
            {
                $nameParts = explode( '::', $parts[1] );
                $nameString = array();
                foreach ( $nameParts as $namePart )
                {
                    if ( is_numeric( $namePart ) )
                    {
                        //@todo
                        $nameString[] = $namePart;
                    }
                    else
                    {
                        $nameString[] = $namePart;
                    }
                }
                $name = implode( ', ', $nameString );

                switch ( $parts[0] )
                {
                    case '_fixed':
                        $result = ezpI18n::tr( 'sensor/robot message', 'Completata da %name', false, array( '%name' => $name ) );
                        break;

                    case '_read':
                        $result = ezpI18n::tr( 'sensor/robot message', 'Letta da %name', false, array( '%name' => $name ) );
                        break;

                    case '_closed':
                        $result = ezpI18n::tr( 'sensor/robot message', 'Chiusa da %name', false, array( '%name' => $name ) );
                        break;

                    case '_assigned':
                        $result = ezpI18n::tr( 'sensor/robot message', 'Assegnata a %name', false, array( '%name' => $name ) );
                        break;

                    case '_reopened':
                        $result = ezpI18n::tr( 'sensor/robot message', 'Riaperta da %name', false, array( '%name' => $name ) );
                        break;
                }
            }
            else
            {
                switch ( $parts[0] )
                {
                    case '_fixed':
                        $result = ezpI18n::tr( 'sensor/robot message', 'Completata' );
                        break;

                    case '_read':
                        $result = ezpI18n::tr( 'sensor/robot message', 'Letta' );
                        break;

                    case '_closed':
                        $result = ezpI18n::tr( 'sensor/robot message', 'Chiusa' );
                        break;

                    case '_assigned':
                        $result = ezpI18n::tr( 'sensor/robot message', 'Assegnata' );
                        break;

                    case '_reopened':
                        $result = ezpI18n::tr( 'sensor/robot message', 'Riaperta' );
                        break;
                }
            }
        }
        return $result;
    }

}