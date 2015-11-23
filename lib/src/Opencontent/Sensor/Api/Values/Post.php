<?php

namespace OpenContent\Sensor\Api\Values;


class Post
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $internalId;

    /**
     * @var \DateTime
     */
    public $published;

    /**
     * @var \DateTime
     */
    public $modified;

    /**
     * @var Post\ExpirationInfo
     */
    public $expiringDate;

    /**
     * @var Post\ResolutionInfo
     */
    public $resolution;

    /**
     * @var string
     */
    public $subject;

    /**
     * @var string
     */
    public $description;

    /**
     * @var Post\Type
     */
    public $type;

    /**
     * @var Post\Status\Privacy
     */
    public $privacy;

    /**
     * @var Post\Status\Moderation
     */
    public $moderation;

    /**
     * @var Post\Status
     */
    public $status;

    /**
     * @var Post\WorkflowStatus
     */
    public $workflowStatus;

    /**
     * @var ParticipantCollection
     */
    public $participants;

    /**
     * @var Participant
     */
    public $author;

    /**
     * @var Participant\ReporterCollection
     */
    public $reporter;

    /**
     * @var Participant\ApproverCollection
     */
    public $approvers;

    /**
     * @var Participant\OwnerCollection
     */
    public $owners;

    /**
     * @var Participant\ObserverCollection
     */
    public $observers;

    /**
     * @var Message\TimelineItemCollection
     */
    public $timelineItems;

    /**
     * @var Message\PrivateMessageCollection
     */
    public $privateMessages;

    /**
     * @var Message\CommentCollection
     */
    public $comments;

    /**
     * @var Post\Field\Image
     */
    public $images;

    /**
     * @var Post\Field\Attachment[]
     */
    public $attachments;

    /**
     * @var Post\Field\Category[]
     */
    public $categories;

    /**
     * @var Post\Field\GeoLocation
     */
    public $geoLocation;

    /**
     * @var Post\Field\Area[]
     */
    public $areas;

}