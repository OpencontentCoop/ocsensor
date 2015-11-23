<?php

namespace OpenContent\Sensor\Core;

use OpenContent\Sensor\Api\Repository as RepositoryInterface;
use OpenContent\Sensor\Api\Action\ActionDefinition;
use OpenContent\Sensor\Api\Permission\PermissionDefinition;
use OpenContent\Sensor\Api\Values\Post;
use OpenContent\Sensor\Api\Values\User;
use OpenContent\Sensor\Core\ActionService;
use OpenContent\Sensor\Core\MessageService;
use OpenContent\Sensor\Core\ParticipantService;
use OpenContent\Sensor\Core\PermissionService;
use OpenContent\Sensor\Core\PostService;
use OpenContent\Sensor\Core\SearchService;

abstract class Repository implements RepositoryInterface
{

    /**
     * @var User
     */
    protected $user;

    /**
     * @var PostService
     */
    protected $postService;

    /**
     * @var MessageService
     */
    protected $messageService;

    /**
     * @var SearchService
     */
    protected $searchService;

    /**
     * @var ParticipantService
     */
    protected $participantService;

    /**
     * @var PermissionService
     */
    protected $permissionService;

    /**
     * @var ActionService
     */
    protected $actionService;

    /**
     * @var PermissionDefinition[]
     */
    protected $permissionDefinitions;

    /**
     * @var ActionDefinition[]
     */
    protected $actionDefinitions;

    /**
     * @param ActionDefinition[] $actionDefinitions
     *
     * @return void
     */
    abstract public function setActionDefinitions( $actionDefinitions );

    /**
     * @param PermissionDefinition[] $permissionDefinitions
     *
     * @return void
     */
    abstract public function setPermissionDefinitions( $permissionDefinitions );

    public function getCurrentUser()
    {
        return $this->user;
    }

    public function setCurrentUser( User $user )
    {
        $this->user = $user;
    }

    public function isUserParticipant( Post $post )
    {
        return $post->participants->getParticipantById( $this->user->id );
    }

    public function getPostService()
    {
        if ( $this->postService === null )
        {
            //@todo
        }
        return $this->postService;
    }

    public function getMessageService()
    {
        if ( $this->messageService === null )
        {
            //@todo
        }
        return $this->messageService;
    }

    public function getSearchService()
    {
        if ( $this->searchService === null )
        {
            //@todo
        }
        return $this->searchService;
    }

    public function getParticipantService()
    {
        if ( $this->participantService === null )
        {
            //@todo
        }
        return $this->participantService;
    }

    public function getPermissionService()
    {
        if ( $this->permissionService === null )
        {
            $this->permissionService = new PermissionService( $this, $this->permissionDefinitions );
        }
        return $this->permissionService;
    }

    public function getActionService()
    {
        if ( $this->actionService === null )
        {
            $this->actionService = new ActionService( $this, $this->actionDefinitions );
        }
        return $this->actionService;
    }
}