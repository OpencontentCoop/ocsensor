<?php

namespace OpenContent\Sensor\Api\Action;

use OpenContent\Sensor\Api\Action\ActionDefinitionParameter;
use OpenContent\Sensor\Api\Action\Action;
use OpenContent\Sensor\Api\Exception\InvalidParameterException;
use OpenContent\Sensor\Api\Exception\PermissionException;
use OpenContent\Sensor\Api\Permission\PermissionDefinition;
use OpenContent\Sensor\Api\Values\Post;
use OpenContent\Sensor\Api\Values\User;

abstract class ActionDefinition
{
    public $identifier;

    public $inputName;

    /**
     * @var ActionDefinitionParameter[]
     */
    public $parameterDefinitions = array();

    /**
     * @var string[]
     */
    public $permissionDefinitionIdentifiers = array();

    /**
     * @param \OpenContent\Sensor\Api\Action\Action $action
     * @param Post $post
     * @param User $user
     *
     * @throws InvalidParameterException
     * @throws PermissionException
     */
    public function dryRun( Action $action, Post $post, User $user )
    {
        $this->checkPermission( $post, $user );
        $this->checkParameters( $action );
    }

    abstract public function run( Action $action, Post $post, User $user );

    protected function checkPermission( Post $post, User $user )
    {
        foreach( $this->permissionDefinitionIdentifiers as $permissionDefinitionIdentifier )
        {
            if ( !$user->permissions->hasPermission( $permissionDefinitionIdentifier ) )
                throw new PermissionException( $permissionDefinitionIdentifier, $user, $post );
        }
    }

    protected function checkParameters( Action $action )
    {
        foreach( $this->parameterDefinitions as $parameterDefinition )
        {
            if ( !$action->hasParameter( $parameterDefinition->identifier ) )
            {
                if ( $parameterDefinition->isRequired )
                    throw new InvalidParameterException( $parameterDefinition );
                else
                    $action->setParameter( $parameterDefinition->identifier, $parameterDefinition->defaultValue );
            }

        }
        return $action;
    }
}