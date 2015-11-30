<?php

namespace OpenContent\Sensor\Legacy\Values;

use OpenContent\Sensor\Api\Values\User as BaseUser;
use eZCollaborationGroup;
use eZPersistentObject;
use eZUser;

class User extends BaseUser
{
    const MAIN_COLLABORATION_GROUP_NAME = 'Sensor';

    const TRASH_COLLABORATION_GROUP_NAME = 'Trash';

    public $whatsAppId;

    /**
     * @return eZCollaborationGroup
     */
    public function getMainCollaborationGroup()
    {
        return $this->getCollaborationGroup( self::MAIN_COLLABORATION_GROUP_NAME );
    }

    public function getTrashCollaborationGroup()
    {
        return $this->getCollaborationGroup( self::TRASH_COLLABORATION_GROUP_NAME );
    }

    protected function getCollaborationGroup( $groupName )
    {
        $group = eZPersistentObject::fetchObject(
            eZCollaborationGroup::definition(),
            null,
            array(
                'user_id' => $this->id,
                'title' => $groupName
            )
        );
        if ( !$group instanceof eZCollaborationGroup && $groupName != '' )
        {
            $group = eZCollaborationGroup::instantiate(
                $this->id,
                $groupName
            );
        }
        return $group;
    }

}