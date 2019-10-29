<?php

class SensorUserInfo extends SocialUser
{
    const MAIN_COLLABORATION_GROUP_NAME = 'Sensor';
    const TRASH_COLLABORATION_GROUP_NAME = 'Trash';

    private static $_cache = array();

    /**
     * @return SensorUserInfo
     */
    public static function current()
    {
       if ( !isset( self::$_cache[eZUser::currentUserID()] ) )
       {
           self::$_cache[eZUser::currentUserID()] = new SensorUserInfo( eZUser::currentUser() );
       }
       return self::$_cache[eZUser::currentUserID()];
    }

    /**
     * @return SensorUserInfo
     */
    public static function instance( eZUser $user )
    {
       if ( !$user instanceof eZUser )
       {
           throw new Exception( "User not found" );
       }
       if ( !isset( self::$_cache[$user->id()] ) )
       {
           self::$_cache[$user->id()] = new SensorUserInfo( $user );
       }
       return self::$_cache[$user->id()];
    }

}
