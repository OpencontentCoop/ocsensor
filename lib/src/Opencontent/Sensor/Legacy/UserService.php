<?php

namespace OpenContent\Sensor\Legacy;

use OpenContent\Sensor\Core\UserService as UserServiceBase;
use OpenContent\Sensor\Api\Values\Post;
use OpenContent\Sensor\Legacy\Values\User as User;
use eZUser;
use eZCollaborationItemStatus;
use SocialUser;

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
            $user->ezUser = eZUser::fetch( $id );
            if ( $user->ezUser instanceof eZUser )
            {
                $user->email = $user->ezUser->Email;
                $user->name = $user->ezUser->contentObject()->name( false, $this->repository->getCurrentLanguage() );
                $user->isEnabled = $user->ezUser->isEnabled();
                $socialUser = SocialUser::instance($user->ezUser);
                $user->commentMode = !$socialUser->hasDenyCommentMode();
                $user->moderationMode = $socialUser->hasModerationMode();
            }
            $this->users[$id] = $user;
        }
        return $this->users[$id];
    }

    public function setUserPostAware( $user, Post $post )
    {
        if ( is_numeric( $user ) )
            $user = $this->loadUser( $user );

        $itemStatus = eZCollaborationItemStatus::fetch( $post->internalId, $user->id );
        if ( $itemStatus instanceof eZCollaborationItemStatus )
        {
            $user->lastAccessDateTime = Utils::getDateTimeFromTimestamp( $itemStatus->attribute( 'last_read' ) );
        }
        $user->permissions = $this->repository->getPermissionService()->loadUserPostPermissionCollection( $user, $post );
        return $user;
    }

    public function setBlockMode( \OpenContent\Sensor\Api\Values\User $user, $enable = true )
    {
        $socialUser = SocialUser::instance($this->loadUser($user->id)->ezUser);
        $socialUser->setBlockMode($enable);
        $user->isEnabled = $enable;
    }

    public function setCommentMode( \OpenContent\Sensor\Api\Values\User $user, $enable = true )
    {
        $socialUser = SocialUser::instance($this->loadUser($user->id)->ezUser);
        $socialUser->setDenyCommentMode(!$enable);
        $user->commentMode = $enable;
    }

    public function setBehalfOfMode( \OpenContent\Sensor\Api\Values\User $user, $enable = true )
    {
        $socialUser = SocialUser::instance($this->loadUser($user->id)->ezUser);
        $socialUser->setCanBehalfOfMode($enable);
        $user->behalfOfMode = $enable;
    }

    public function getAlerts( \OpenContent\Sensor\Api\Values\User $user )
    {
        $socialUser = SocialUser::instance($this->loadUser($user->id)->ezUser);
        return $socialUser->attribute( 'alerts' );
    }

    public function addAlerts( \OpenContent\Sensor\Api\Values\User $user, $message, $level )
    {
        $socialUser = SocialUser::instance($this->loadUser($user->id)->ezUser);
        $socialUser->addFlashAlert($message, $level);
    }
}