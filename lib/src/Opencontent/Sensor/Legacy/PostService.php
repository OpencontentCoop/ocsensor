<?php

namespace OpenContent\Sensor\Legacy;

use OpenContent\Sensor\Api\Values\Participant;
use OpenContent\Sensor\Core\PostService as PostServiceBase;
use OpenContent\Sensor\Api\Values\Post;
use OpenContent\Sensor\Api\Values\PostCreateStruct;
use OpenContent\Sensor\Api\Values\PostUpdateStruct;
use OpenContent\Sensor\Api\Exception\BaseException;
use eZPersistentObject;
use eZCollaborationItem;
use eZContentObject;
use eZContentObjectAttribute;
use eZContentObjectState;
use eZImageAliasHandler;
use DateTime;
use DateInterval;
use ezpI18n;


class PostService extends PostServiceBase
{
    const COLLABORATION_FIELD_OBJECT_ID = 'data_int1';

    const COLLABORATION_FIELD_LAST_CHANGE = 'data_int2';

    const COLLABORATION_FIELD_STATUS = 'data_int3';

    const COLLABORATION_FIELD_HANDLER = 'data_text1';

    const COLLABORATION_FIELD_EXPIRY = 'data_text3';

    const SITE_DATA_FIELD_PREFIX = 'sensorpost_';


    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var eZContentObject
     */
    protected $contentObject;

    /**
     * @var eZContentObjectAttribute[]
     */
    protected $contentObjectDataMap;

    /**
     * @var eZCollaborationItem
     */
    protected $collaborationItem;

    public function loadPost( $postId )
    {
        $type = $this->repository->getSensorCollaborationHandlerTypeString();
        $collaborationItem = eZPersistentObject::fetchObject(
            eZCollaborationItem::definition(),
            null,
            array(
                'type_identifier' => $type,
                'data_int1' => intval( $postId )
            )
        );
        $contentObject = eZContentObject::fetch( $postId );
        if ( $collaborationItem instanceof eZCollaborationItem && $contentObject instanceof eZContentObject )
        {
            return $this->internalLoadPost( $collaborationItem, $contentObject );
        }
        throw new BaseException( "eZCollaborationItem $type not found for object $postId" );
    }

    public function loadPostByInternalId( $postInternalId )
    {
        $type = $this->repository->getSensorCollaborationHandlerTypeString();
        $collaborationItem = eZPersistentObject::fetchObject(
            eZCollaborationItem::definition(),
            null,
            array(
                'type_identifier' => $type,
                'id' => intval( $postInternalId )
            )
        );
        if ( $collaborationItem instanceof eZCollaborationItem )
        {
            $contentObject = eZContentObject::fetch( $collaborationItem->attribute( 'data_int1' ) );
            if ( $contentObject instanceof eZContentObject )
                return $this->internalLoadPost( $collaborationItem, $contentObject );
        }
        throw new BaseException( "eZCollaborationItem $type not found for id $postInternalId" );
    }

    protected function internalLoadPost( eZCollaborationItem $collaborationItem, eZContentObject $contentObject )
    {
        $this->collaborationItem = $collaborationItem;
        $this->contentObject = $contentObject;
        $this->contentObjectDataMap = $contentObject->attribute( 'data_map' );

        $post = new Post();
        $post->id = $this->contentObject->attribute( 'id' );
        $post->internalId = $this->collaborationItem->attribute( 'id' );

        $post->published = Utils::getDateTimeFromTimestamp( $this->contentObject->attribute( 'published' ) );
        $post->modified = Utils::getDateTimeFromTimestamp( $this->contentObject->attribute( 'modified' ) );;

        $post->expiringInfo = $this->getPostExpirationInfo();
        $post->resolutionInfo = $this->getPostResolutionInfo( $post );

        $post->subject = $this->contentObject->attribute( 'name' );
        $post->description = $this->getPostDescription();
        $post->type = $this->getPostType();

        $post->privacy = $this->getPostPrivacyCurrentStatus();
        $post->status = $this->getPostCurrentStatus();
        $post->moderation = $this->getPostModerationCurrentStatus();
        $post->workflowStatus = $this->getPostWorkflowStatus();

        $post->images = $this->getPostImages();
        $post->attachments = $this->getPostAttachments();
        $post->categories = $this->getPostCategories();
        $post->areas = $this->getPostAreas();
        $post->geoLocation = $this->getPostGeoLocation();

        $post->comments = $this->repository->getMessageService()->loadCommentCollectionByPost( $post );
        $post->privateMessages = $this->repository->getMessageService()->loadPrivateMessageCollectionByPost( $post );
        $post->timelineItems = $this->repository->getMessageService()->loadTimelineItemCollectionByPost( $post );

        $post->participants = $this->repository->getParticipantService()->loadPostParticipants( $post );
        $post->reporter = $this->repository->getParticipantService()->loadPostReporter( $post );
        $post->approvers = $this->repository->getParticipantService()->loadPostApprovers( $post );
        $post->owners = $this->repository->getParticipantService()->loadPostOwners( $post );
        $post->observers = $this->repository->getParticipantService()->loadPostObservers( $post );

        $post->author = clone $post->reporter;
        $authorName = $this->getPostAuthorName();
        if ( $authorName )
            $post->author->name = $authorName;

        return $post;
    }

    protected function getPostAuthorName()
    {
        $authorName = false;
        if ( isset( $this->contentObjectDataMap['on_behalf_of'] ) && $this->contentObjectDataMap['on_behalf_of']->hasContent() )
        {
            $authorName = $this->contentObjectDataMap['on_behalf_of']->toString();
            if ( isset( $this->contentObjectDataMap['on_behalf_of_detail'] ) && $this->contentObjectDataMap['on_behalf_of_detail']->hasContent() )
                $authorName .= ', ' . $this->contentObjectDataMap['on_behalf_of_detail']->toString();
        }
        return $authorName;
    }

    protected function getPostWorkflowStatus()
    {
        return Post\WorkflowStatus::instanceByCode( $this->collaborationItem->attribute( self::COLLABORATION_FIELD_STATUS ) );
    }

    protected function getPostExpirationInfo()
    {
        $publishedDateTime = Utils::getDateTimeFromTimestamp( $this->contentObject->attribute( 'published' ) );
        $expirationDateTime = Utils::getDateTimeFromTimestamp( intval( $this->collaborationItem->attribute( self::COLLABORATION_FIELD_EXPIRY ) ) );

        $diffResult = Utils::getDateDiff( $expirationDateTime );
        if ( $diffResult->interval->invert )
        {
            $expirationText = ezpI18n::tr( 'sensor/expiring', 'Scaduto da' );
            $expirationLabel = 'danger';
        }
        else
        {
            $expirationText = ezpI18n::tr( 'sensor/expiring', 'Scade fra' );
            $expirationLabel = 'default';
        }
        $expirationText = $expirationText . ' ' . $diffResult->getText();

        $expirationInfo = new Post\ExpirationInfo();
        $expirationInfo->creationDateTime = $publishedDateTime;
        $expirationInfo->expirationDateTime = $expirationDateTime;
        $expirationInfo->label = $expirationLabel;
        $expirationInfo->text = $expirationText;
        $diff = $expirationDateTime->diff( $publishedDateTime );
        if ( $diff instanceof DateInterval )
        {
            $expirationInfo->days = $diff->days;
        }
        return $expirationInfo;
    }

    protected function getPostResolutionInfo( Post $post )
    {
        $resolutionInfo = null;
        if ( $this->getPostWorkflowStatus()->code == Post\WorkflowStatus::CLOSED )
        {
            $lastTimelineItem = $this->repository->getMessageService()->loadTimelineItemCollectionByPost( $post )->lastMessage;
            $diffResult = Utils::getDateDiff( $post->published, $lastTimelineItem->published );
            $resolutionInfo = new Post\ResolutionInfo();
            $resolutionInfo->resolutionDateTime = $lastTimelineItem->published;
            $resolutionInfo->creationDateTime = $post->published;
            $resolutionInfo->text = $diffResult->getText();
        }
        return $resolutionInfo;
    }

    protected function getPostType()
    {
        $type = null;
        if ( isset( $this->contentObjectDataMap['type'] ) )
        {
            $typeIdentifier = $this->contentObjectDataMap['type']->toString();
            $type = new Post\Type();
            $type->identifier = $typeIdentifier;
            switch ( $typeIdentifier )
            {
                case 'suggerimento':
                    $type->name = ezpI18n::tr( 'openpa_sensor/type', 'Suggerimento' );
                    $type->label = 'warning';
                    break;

                case 'reclamo':
                    $type->name = ezpI18n::tr( 'openpa_sensor/type', 'Reclamo' );
                    $type->label = 'danger';
                    break;

                case 'segnalazione':
                    $type->name = ezpI18n::tr( 'openpa_sensor/type', 'Segnalazione' );
                    $type->label = 'info';
                    break;

                default:
                    $type->name = ucfirst( $typeIdentifier );
                    $type->label = 'info';
            }
        }
        return $type;
    }

    protected function getPostCurrentStatusByGroupIdentifier( $identifier )
    {
        foreach( $this->repository->getSensorPostStates( $identifier ) as $state )
        {
            if ( in_array( $state->attribute( 'id' ), $this->contentObject->attribute( 'state_id_array' ) ) )
            {
                return $state;
            }
        }
        return null;
    }

    protected function getPostCurrentStatus()
    {
        $status = new Post\Status();
        $state = $this->getPostCurrentStatusByGroupIdentifier( 'sensor' );
        if ( $state instanceof eZContentObjectState )
        {
            $status->identifier = $state->attribute( 'identifier' );
            $status->name = $state->currentTranslation()->attribute( 'name' );
            $status->label = 'info';
            if ( $state->attribute( 'identifier' ) == 'pending' )
            {
                $status->label = 'danger';
            }
            elseif ( $state->attribute( 'identifier' ) == 'open' )
            {
                $status->label = 'warning';
            }
            elseif ( $state->attribute( 'identifier' ) == 'close' )
            {
                $status->label = 'success';
            }

        }
        return $status;
    }

    protected function getPostPrivacyCurrentStatus()
    {
        $status = new Post\Status\Privacy();
        $state = $this->getPostCurrentStatusByGroupIdentifier( 'privacy' );
        if ( $state instanceof eZContentObjectState )
        {
            $status->identifier = $state->attribute( 'identifier' );
            $status->name = $state->currentTranslation()->attribute( 'name' );
            $status->label = 'info';
            if ( $state->attribute( 'identifier' ) == 'private' )
            {
                $status->label = 'default';
            }
        }
        return $status;
    }

    protected function getPostModerationCurrentStatus()
    {
        $status = new Post\Status\Moderation();
        $state = $this->getPostCurrentStatusByGroupIdentifier( 'moderation' );
        if ( $state instanceof eZContentObjectState )
        {
            $status->identifier = $state->attribute( 'identifier' );
            $status->name = $state->currentTranslation()->attribute( 'name' );
            $status->label = 'danger';
        }
        return $status;
    }

    protected function getPostImages()
    {
        $data = array();
        if ( isset( $this->contentObjectDataMap['image'] ) && $this->contentObjectDataMap['image']->hasContent() )
        {
            /** @var eZImageAliasHandler $content */
            $content = $this->contentObjectDataMap['image']->content();
            $image = new Post\Field\Image();
            $image->fileName = $content->attribute( 'original_filename' );
            $structure = array(
                'width' => null,
                'height' => null,
                'mime_typ' => null,
                'filename' => null,
                'suffix' => null,
                'url' => null,
                'filesize' => null
            );
            $original = array_intersect_key( $content->attribute( 'original' ), $structure );
            $small = array_intersect_key( $content->attribute( 'small' ), $structure );
            $image->original = $original;
            $image->thumbnail = $small;
            $data[] = $image;
        }
        return $data;
    }

    protected function getPostAttachments()
    {
        $data = array();
        if ( isset( $this->contentObjectDataMap['attachment'] ) && $this->contentObjectDataMap['attachment']->hasContent() )
        {
            $attachment = new Post\Field\Attachment();
            $data[] = $attachment;
        }
        return $data;
    }

    protected function getPostCategories()
    {
        $data = array();
        if ( isset( $this->contentObjectDataMap['category'] ) && $this->contentObjectDataMap['category']->hasContent() )
        {
            $relationIds = explode( '-',  $this->contentObjectDataMap['category']->toString() );
            /** @var eZContentObject[] $objects */
            $objects = eZContentObject::fetchIDArray( $relationIds );
            foreach( $objects as $object )
            {
                $category = new Post\Field\Category();
                $category->id = $object->attribute( 'id' );
                $category->name = $object->attribute( 'name' );
                $data[] = $category;
            }
        }
        return $data;
    }

    protected function getPostAreas()
    {
        $data = array();
        if ( isset( $this->contentObjectDataMap['area'] ) && $this->contentObjectDataMap['area']->hasContent() )
        {
            $relationIds = explode( '-',  $this->contentObjectDataMap['area']->toString() );
            /** @var eZContentObject[] $objects */
            $objects = eZContentObject::fetchIDArray( $relationIds );
            foreach( $objects as $object )
            {
                $area = new Post\Field\Area();
                $area->id = $object->attribute( 'id' );
                $area->name = $object->attribute( 'name' );
                $data[] = $area;
            }
        }
        return $data;
    }

    protected function getPostGeoLocation()
    {
        $geo = new Post\Field\GeoLocation();
        if ( isset( $this->contentObjectDataMap['geo'] ) && $this->contentObjectDataMap['geo']->hasContent() )
        {
            /** @var \eZGmapLocation $content */
            $content = $this->contentObjectDataMap['geo']->content();
            $geo->latitude = $content->attribute( 'latitude' );
            $geo->longitude = $content->attribute( 'longitude' );
        }
        return $geo;
    }

    protected function getPostDescription()
    {
        if ( isset( $this->contentObjectDataMap['description'] ) )
            return $this->contentObjectDataMap['description']->toString();
        return false;
    }

    public function createPost( PostCreateStruct $post )
    {
        // TODO: Implement createPost() method.
    }

    public function updatePost( PostUpdateStruct $post )
    {
        // TODO: Implement updatePost() method.
    }

    public function deletePost( Post $post )
    {
        // TODO: Implement deletePost() method.
    }

    public function trashPost( Post $post )
    {
        // TODO: Implement trashPost() method.
    }

    public function restorePost( Post $post )
    {
        // TODO: Implement restorePost() method.
    }

    public function refreshPost( Post $post )
    {
        // TODO: Implement refreshPost() method.
    }

    public function setPostStatus( Post $post, Post\Status $status )
    {
        // TODO: Implement setPostStatus() method.
    }

    public function setPostWorkflowStatus( Post $post, Post\WorkflowStatus $status )
    {
        // TODO: Implement setPostWorkflowStatus() method.
    }

    public function setPostExpirationInfo( Post $post, Post\ExpirationInfo $expiry )
    {
        // TODO: Implement setPostExpirationInfo() method.
    }

}
