<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 24/11/15
 * Time: 12:52
 */

namespace OpenContent\Sensor\Legacy;

use OpenContent\Sensor\Core\UserService as UserServiceBase;
use OpenContent\Sensor\Api\Values\Post;
use OpenContent\Sensor\Api\Values\User;
use eZUser;
use eZCollaborationItemStatus;

class UserService extends UserServiceBase
{
    /**
     * @var User[]
     */
    protected $users = array();

    public function loadUser( $id )
    {
        if ( !isset( $this->users[$id] ) )
        {
            $user = new User();
            $user->id = $id;
            $ezUser = eZUser::fetch( $id );
            if ( $ezUser instanceof eZUser )
            {
                $user->email = $ezUser->Email;
                $user->name = $ezUser->contentObject()->name( false, $this->repository->getCurrentLanguage() );
            }
            $this->users[$id] = $user;
        }
        return $this->users[$id];
    }

    public function loadUserPostAware( $user, Post $post )
    {
        if ( is_numeric( $user ) )
            $user = $this->loadUser( $user );

        $itemStatus = eZCollaborationItemStatus::fetch( $post->internalId, $user->id );
        if ( $itemStatus instanceof eZCollaborationItemStatus )
        {
            $user->lastAccessDateTime = Utils::getDateTimeFromTimestamp( $itemStatus->attribute( 'last_read' ) );
        }
        $this->repository->getPermissionService()->loadUserPostPermissionCollection( $user, $post );
        return $user;
    }
}