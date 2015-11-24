<?php

namespace OpenContent\Sensor\Core\PermissionDefinitions;

use OpenContent\Sensor\Api\Permission\PermissionDefinition;
use OpenContent\Sensor\Api\Values\Post;
use OpenContent\Sensor\Api\Values\User;

class Test extends PermissionDefinition
{
    public $identifier = 'can_test';

    public function userHasPermission( User $user, Post $post )
    {
        return true;
    }
}