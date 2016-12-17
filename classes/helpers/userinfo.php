<?php

class SensorUserInfo extends SocialUser
{
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

    public function participateAs( SensorPost $post, $role )
    {
        $link = eZCollaborationItemParticipantLink::fetch(
            $post->getCollaborationItem()->attribute( 'id' ),
            $this->user()->id()
        );

        if ( !$link instanceof eZCollaborationItemParticipantLink )
        {
            $link = eZCollaborationItemParticipantLink::create(
                $post->getCollaborationItem()->attribute( 'id' ),
                $this->user()->id(),
                $role,
                eZCollaborationItemParticipantLink::TYPE_USER
            );
            $link->store();
            $group = $this->sensorCollaborationGroup();
            eZCollaborationItemGroupLink::addItem(
                $group->attribute( 'id' ),
                $post->getCollaborationItem()->attribute( 'id' ),
                $this->user()->id()
            );
            $post->getCollaborationItem()->setIsActive( true, $this->user()->id() );
        }
        else
        {
            $link->setAttribute( 'participant_role', $role );
            $link->sync();
        }
        $GLOBALS['eZCollaborationItemParticipantLinkListCache'] = array();
    }

    public function restoreParticipation( SensorPost $post )
    {
        /** @var eZCollaborationItemGroupLink $group */
        $groupLink = eZPersistentObject::fetchObject(
            eZCollaborationItemGroupLink::definition(),
            null,
            array( 'collaboration_id' => $post->getCollaborationItem()->attribute( 'id' ),
                   'user_id' => $this->user()->id()
            )
        );
        if ( $groupLink instanceof eZCollaborationItemGroupLink )
        {
            $db = eZDB::instance();
            $db->begin();
            $groupLink->remove();
            $sensorGroup = $this->sensorCollaborationGroup();
            $sensorGroupLink = eZCollaborationItemGroupLink::create(
                $post->getCollaborationItem()->attribute( 'id' ),
                $sensorGroup->attribute( 'id' ),
                $this->user()->id()
            );
            $sensorGroupLink->store();
            $db->commit();
        }
    }

    public function trashParticipation( SensorPost $post )
    {
        /** @var eZCollaborationItemGroupLink $group */
        $groupLink = eZPersistentObject::fetchObject(
            eZCollaborationItemGroupLink::definition(),
            null,
            array( 'collaboration_id' => $post->getCollaborationItem()->attribute( 'id' ),
                   'user_id' => $this->user()->id()
            )
        );
        if ( $groupLink instanceof eZCollaborationItemGroupLink )
        {
            $db = eZDB::instance();
            $db->begin();
            $groupLink->remove();
            $trashGroup = $this->trashCollaborationGroup();
            $trashGroupLink = eZCollaborationItemGroupLink::create(
                $post->getCollaborationItem()->attribute( 'id' ),
                $trashGroup->attribute( 'id' ),
                $this->user()->id()
            );
            $trashGroupLink->store();
            $db->commit();
        }
    }

    /**
     * @return eZCollaborationGroup
     */
    public function sensorCollaborationGroup()
    {
        return $this->getCollaborationGroup( eZINI::instance( 'ocsensor.ini' )->variable( 'SensorConfig', 'CollaborationGroupName' ));
    }

    public function trashCollaborationGroup()
    {
        return $this->getCollaborationGroup( self::TRASH_COLLABORATION_GROUP_NAME );
    }

    protected function getCollaborationGroup( $groupName )
    {
        $group = eZPersistentObject::fetchObject(
            eZCollaborationGroup::definition(),
            null,
            array(
                'user_id' => $this->user()->id(),
                'title' => $groupName
            )
        );
        if ( !$group instanceof eZCollaborationGroup && $groupName != '' )
        {
            $group = eZCollaborationGroup::instantiate(
                $this->user()->id(),
                $groupName
            );
        }
        return $group;
    }

    protected function getChildCollaborationGroup( $parentGroup, $groupName )
    {
        $group = eZPersistentObject::fetchObject(
            eZCollaborationGroup::definition(),
            null,
            array(
                'user_id' => $this->user()->id(),
                'title' => $groupName
            )
        );
        if ( !$group instanceof eZCollaborationGroup )
        {
            /** @var eZCollaborationGroup $parentGroup */
            $group = eZCollaborationGroup::create( $this->user()->id(), $groupName );
            $parentGroup->addChild( $group );
        }
        return $group;
    }
}
