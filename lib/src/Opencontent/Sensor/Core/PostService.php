<?php

namespace OpenContent\Sensor\Core;

use OpenContent\Sensor\Api\PostService as PostServiceInterface;
use OpenContent\Sensor\Api\Values\Post;
use OpenContent\Sensor\Api\Values\PostCreateStruct;
use OpenContent\Sensor\Api\Values\PostUpdateStruct;

class PostService implements PostServiceInterface
{
    public function loadPost( $postId )
    {
        // TODO: Implement loadPost() method.
    }

    public function loadPostByInternalId( $postInternalId )
    {
        // TODO: Implement loadPostByInternalId() method.
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