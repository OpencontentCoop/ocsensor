<?php

namespace OpenContent\Sensor\Api\Values;
use OpenContent\Sensor\Api\Values\PermissionCollection;

class User
{
    public $id;

    public $name;

    public $email;

    /**
     * @var PermissionCollection
     */
    public $permissions;
}