<?php

namespace OpenContent\Sensor\Api;

use OpenContent\Sensor\Api\Values\Message;
use OpenContent\Sensor\Api\Values\Post;

interface MessageService
{

    /**
     * @param Post $post
     *
     * @return Message/CommentCollection
     */
    public function loadCommentCollectionByPost( Post $post );


    /**
     * @param Post $post
     *
     * @return Message/PrivateMessageCollection
     */
    public function loadPrivateMessageCollectionByPost( Post $post );

    /**
     * @param Post $post
     *
     * @return Message/TimelineItemCollection
     */
    public function loadTimelineItemCollectionByPost( Post $post );

    public function addTimelineItemByWorkflowStatus( Post $post, $status, $parameters = null );

    public function createTimelineItem( Message\TimelineItemStruct $struct );

    public function createPrivateMessage( Message\PrivateMessageStruct $struct );

    public function createComment( Message\CommentStruct $struct );

}