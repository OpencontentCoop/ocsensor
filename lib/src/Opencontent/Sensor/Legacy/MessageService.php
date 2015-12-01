<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 23/11/15
 * Time: 18:58
 */

namespace OpenContent\Sensor\Legacy;

use OpenContent\Sensor\Api\Values\Message;
use OpenContent\Sensor\Api\Values\Participant;
use OpenContent\Sensor\Api\Values\User;
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
     * @var Repository
     */
    protected $repository;

    /**
     * @var int
     */
    protected $countMessagesByPost = array();

    /**
     * @var Message\CommentCollection[]
     */
    protected $commentsByPost = array();

    /**
     * @var Message\PrivateMessageCollection[]
     */
    protected $privateMessagesByPost = array();

    /**
     * @var Message\TimelineItemCollection[]
     */
    protected $timelineItemsByPost = array();

    public function loadCommentCollectionByPost( Post $post )
    {
        $this->internalLoadMessagesByPost( $post );
        return $this->commentsByPost[$post->internalId];
    }

    public function loadPrivateMessageCollectionByPost( Post $post )
    {
        $this->internalLoadMessagesByPost( $post );
        return $this->privateMessagesByPost[$post->internalId];
    }

    public function loadTimelineItemCollectionByPost( Post $post )
    {
        $this->internalLoadMessagesByPost( $post );
        return $this->timelineItemsByPost[$post->internalId];
    }

    protected function internalLoadMessagesByPost( Post $post )
    {
        $postInternalId = $post->internalId;
        if ( !isset( $this->countMessagesByPost[$postInternalId] ) )
        {
            $this->countMessagesByPost[$postInternalId] = 0;
            $this->commentsByPost[$postInternalId] = new Message\CommentCollection();
            $this->privateMessagesByPost[$postInternalId] = new Message\PrivateMessageCollection();
            $this->timelineItemsByPost[$postInternalId] = new Message\TimelineItemCollection();


            /** @var eZCollaborationItemMessageLink[] $messageLinks */
            $messageLinks = eZPersistentObject::fetchObjectList(
                eZCollaborationItemMessageLink::definition(),
                null,
                array( 'collaboration_id' => $postInternalId ),
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
                array( 'id' => array( $simpleMessageIdList ) ),
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
                        $message->text = $this->formatTimelineItemText( $simpleMessage, $post );
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
                            $message->receivers[] = $this->repository->getParticipantService()
                                                                     ->loadPostParticipants( $post )
                                                                     ->getUserById( $link->attribute( 'participant_id' ) );
                        }
                    }
                    $message->id = $simpleMessage->attribute( 'id' );
                    $creator = new User();
                    $creator->id = $simpleMessage->attribute( 'id' );
                    $creator = $this->repository->getParticipantService()
                                                           ->loadPostParticipants( $post )
                                                           ->getUserById( $simpleMessage->attribute( 'creator_id' ) );
                    if ( $creator instanceof User )
                        $message->creator = $creator;
                    else
                        $message->creator = $this->repository->getUserService()->loadUser(
                            $simpleMessage->attribute( 'creator_id' )
                        );

                    $message->published = Utils::getDateTimeFromTimestamp( $simpleMessage->attribute( 'created' ) );
                    $message->modified = Utils::getDateTimeFromTimestamp( $simpleMessage->attribute( 'modified' ) );

                    if ( $type == 'comment' )
                        $this->commentsByPost[$postInternalId]->addMessage( $message );

                    elseif ( $type == 'timeline' )
                        $this->timelineItemsByPost[$postInternalId]->addMessage( $message );

                    elseif ( $type == 'private' )
                        $this->privateMessagesByPost[$postInternalId]->addMessage( $message );

                    $this->countMessagesByPost[$postInternalId]++;
                }
            }
        }
    }

    protected function formatTimelineItemText( eZCollaborationSimpleMessage $simpleMessage, Post $post )
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
                        $participant = $this->repository->getParticipantService()
                                         ->loadPostParticipants( $post )
                                         ->getUserById( intval( $namePart ) );
                        $nameString[] = $participant->name;
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

    public function createTimelineItem( Message\TimelineItemStruct $struct )
    {
        //@todo
    }

    public function createPrivateMessage( Message\PrivateMessageStruct $struct )
    {
        //@todo
    }

    public function createComment( Message\CommentStruct $struct )
    {
        //@todo
    }

}