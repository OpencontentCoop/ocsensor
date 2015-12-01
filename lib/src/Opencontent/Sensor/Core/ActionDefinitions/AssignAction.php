<?php

namespace OpenContent\Sensor\Core\ActionDefinitions;

use OpenContent\Sensor\Api\Action\Action;
use OpenContent\Sensor\Api\Action\ActionDefinition;
use OpenContent\Sensor\Api\Action\ActionDefinitionParameter;
use OpenContent\Sensor\Api\Repository;
use OpenContent\Sensor\Api\Values\Event;
use OpenContent\Sensor\Api\Values\Post;
use OpenContent\Sensor\Api\Values\User;


class AssignAction extends ActionDefinition
{
    public function __construct()
    {
        $this->identifier = 'assign';
        $this->permissionDefinitionIdentifiers = array( 'can_assign' );
        $this->inputName = 'Assign';

        $parameter = new ActionDefinitionParameter();
        $parameter->identifier = 'participant_ids';
        $parameter->isRequired = true;
        $parameter->type = 'array';
        $parameter->inputName = 'SensorItemAssignTo';

        $this->parameterDefinitions = array(
            $parameter
        );
    }

    public function run( Repository $repository, Action $action, Post $post, User $user )
    {

    }
}