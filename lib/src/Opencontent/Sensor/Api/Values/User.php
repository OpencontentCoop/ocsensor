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

    public static function createUserFromId( $id )
    {
        $user = new static();
        $user->id = $id;
        $ezUser = \eZUser::fetch( $id );
        if ( $ezUser instanceof \eZUser )
        {
            $user->email = $ezUser->Email;
            $user->name = $ezUser->contentObject()->attribute( 'name' );
        }
        return $user;
    }

}