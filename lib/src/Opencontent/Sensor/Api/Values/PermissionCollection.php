<?php

namespace OpenContent\Sensor\Api\Values;

use OpenContent\Sensor\Api\Values\Permission;
use OpenContent\Sensor\Api\Exportable;

class PermissionCollection extends Exportable implements \IteratorAggregate
{
    /**
     * @var Permission[]
     */
    protected $permissions;

    public function hasPermission( $identifier )
    {
        return isset( $this->permissions[$identifier] ) ? $this->permissions[$identifier] : false;
    }

    public function addPermission( Permission $permission )
    {
        $this->permissions[$permission->identifier] = $permission->grant;
    }

    public function getIterator()
    {
        return new \ArrayIterator( $this->permissions );
    }
}