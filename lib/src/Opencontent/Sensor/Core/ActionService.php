<?php

namespace OpenContent\Sensor\Core;

use OpenContent\Sensor\Api\Action\ActionDefinition;
use OpenContent\Sensor\Api\Action\Action;
use OpenContent\Sensor\Api\ActionService as ActionServiceInterface;
use OpenContent\Sensor\Api\Repository;
use OpenContent\Sensor\Api\Values\Post;
use OpenContent\Sensor\Api\Values\User;
use OpenContent\Sensor\Api\Exception\BaseException;

class ActionService implements ActionServiceInterface
{

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var ActionDefinition[]
     */
    protected $actionDefinitions;

    /**
     * @var Post
     */
    protected $post;

    /**
     * @var User
     */
    protected $user;

    /**
     * @param Repository $repository
     * @param ActionDefinition[] $actionDefinitions
     */
    public function __construct( Repository $repository, $actionDefinitions )
    {
        $this->repository = $repository;
        $this->actionDefinitions = $actionDefinitions;
    }

    public function loadActionDefinitionByIdentifier( $identifier )
    {
        foreach( $this->actionDefinitions as $actionDefinition )
        {
            if ( $actionDefinition->identifier == $identifier )
                return $actionDefinition;
        }
        throw new BaseException( "Action $identifier not found" );
    }

    public function dryRunAction( Action $action )
    {
        $this->loadActionDefinitionByIdentifier( $action->identifier )->dryRun( $action, $this->post, $this->user );
    }

    public function runAction( Action $action )
    {
        $this->loadActionDefinitionByIdentifier( $action->identifier )->run( $action, $this->post, $this->user );
    }

    public function setPost( Post $post )
    {
        $this->post = $post;
    }

    public function setUser( User $user )
    {
        $this->user = $user;
    }
}