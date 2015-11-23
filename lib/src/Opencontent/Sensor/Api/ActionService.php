<?php

namespace OpenContent\Sensor\Api;

use OpenContent\Sensor\Api\Action\ActionDefinition;
use OpenContent\Sensor\Api\Action\Action;
use OpenContent\Sensor\Api\Values\Post;
use OpenContent\Sensor\Api\Values\User;
use OpenContent\Sensor\Api\Exception\PermissionException;
use OpenContent\Sensor\Api\Exception\InvalidParameterException;

interface ActionService
{
    public function setPost( Post $post );

    public function setUser( User $user );

    /**
     * @param $identifier
     *
     * @return ActionDefinition
     */
    public function loadActionDefinitionByIdentifier( $identifier );

    /**
     * @param Action $action
     *
     * @throws PermissionException
     * @throws InvalidParameterException
     */
    public function dryRunAction( Action $action );

    /**
     * @param Action $action
     *
     * @throws PermissionException
     * @throws InvalidParameterException
     */
    public function runAction( Action $action );

}
