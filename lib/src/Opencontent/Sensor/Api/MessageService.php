<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 23/11/15
 * Time: 09:17
 */

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

}