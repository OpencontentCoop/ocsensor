<?php

namespace OpenContent\Sensor\Api\Values;
use OpenContent\Sensor\Api\Values\PermissionCollection;
use OpenContent\Sensor\Api\Exportable;

class User extends Exportable
{
    public $id;

    public $name;

    public $email;

    /**
     * @var PermissionCollection
     */
    public $permissions;

}