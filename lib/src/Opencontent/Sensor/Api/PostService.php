<?php

namespace OpenContent\Sensor\Api;

use OpenContent\Sensor\Api\Values\Post;
use OpenContent\Sensor\Api\Values\PostCreateStruct;
use OpenContent\Sensor\Api\Values\PostUpdateStruct;


interface PostService
{
    /**
     * @param $postId
     *
     * @return Post
     * @throw \Exception
     */
    public function loadPost( $postId );

    /**
     * @param $postInternalId
     *
     * @return Post
     * @throw \Exception
     */
    public function loadPostByInternalId( $postInternalId );

    /**
     * @param PostCreateStruct $post
     *
     * @return Post
     * @throw \Exception
     */
    public function createPost( PostCreateStruct $post );

    /**
     * @param PostUpdateStruct $post
     *
     * @return Post
     * @throw \Exception
     */
    public function updatePost( PostUpdateStruct $post );

    /**
     * @param Post $post
     *
     * @return true
     * @throw \Exception
     */
    public function deletePost( Post $post );

    /**
     * @param Post $post
     *
     * @return true
     * @throw \Exception
     */
    public function trashPost( Post $post );

    /**
     * @param Post $post
     *
     * @return true
     * @throw \Exception
     */
    public function restorePost( Post $post );

    /**
     * @param Post $post
     *
     */
    public function refreshPost( Post $post );

    /**
     * @param Post $post
     * @param Post\Status $status
     * @throw \Exception
     */
    public function setPostStatus( Post $post, Post\Status $status );

    /**
     * @param Post $post
     * @param Post\WorkflowStatus $status
     * @throw \Exception
     */
    public function setPostWorkflowStatus( Post $post, Post\WorkflowStatus $status );

    /**
     * @param Post $post
     * @param Post\ExpirationInfo $expiry
     * @throw \Exception
     */
    public function setPostExpirationInfo( Post $post, Post\ExpirationInfo $expiry );
}