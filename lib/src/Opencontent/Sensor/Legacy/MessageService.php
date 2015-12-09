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
use OpenContent\Sensor\Utils\TimelineTools;

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
                        $message->text = TimelineTools::getText(
                            $simpleMessage->attribute( 'data_text1' ),
                            $this->repository->getParticipantService()->loadPostParticipants( $post )
                        );
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

    public function addTimelineItemByWorkflowStatus( Post $post, $status, $parameters = null )
    {
        $struct = new Message\TimelineItemStruct();
        $struct->post = $post;
        $struct->creator = $this->repository->getCurrentUser();
        $struct->status = $status;
        $struct->createdDateTime = new \DateTime();
        if ( $parameters === null )
            $parameters = $struct->creator->id;
        $struct->text = TimelineTools::setText( $status, $parameters );
        $this->repository->getMessageService()->createTimelineItem( $struct );
    }


    public function createTimelineItem( Message\TimelineItemStruct $struct )
    {
        $message = eZCollaborationSimpleMessage::create(
            $this->repository->getSensorCollaborationHandlerTypeString() . '_comment',
            $struct->text,
            $struct->creator->id
        );
        $message->store();

        $db = \eZDB::instance();
        $db->begin();
        $messageLink = eZCollaborationItemMessageLink::create( $struct->post->internalId, $message->ID, 0, $struct->creator->id );
        $messageLink->store();
        $db->commit();

        $this->repository->getUserService()->setLastAccessDateTime( $struct->creator, $struct->post );
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