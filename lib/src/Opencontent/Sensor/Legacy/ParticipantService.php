<?php

namespace OpenContent\Sensor\Legacy;

use OpenContent\Sensor\Api\Values\Participant;
use OpenContent\Sensor\Api\Values\Participant\ApproverCollection;
use OpenContent\Sensor\Api\Values\Participant\ObserverCollection;
use OpenContent\Sensor\Api\Values\Participant\OwnerCollection;
use OpenContent\Sensor\Api\Values\ParticipantCollection;
use OpenContent\Sensor\Api\Values\Post;
use OpenContent\Sensor\Core\ParticipantService as ParticipantServiceBase;
use eZContentObject;
use eZCollaborationItemParticipantLink;
use ezpI18n;
use OpenContent\Sensor\Api\Values\ParticipantRoleCollection;
use OpenContent\Sensor\Api\Values\ParticipantRole;

class ParticipantService extends ParticipantServiceBase
{
    /**
     * @var ParticipantCollection[]
     */
    protected $participantsByPost = array();

    /**
     * @var ParticipantRoleCollection
     */
    protected $participantRoles;

    public function loadParticipantRoleCollection()
    {
        if ( $this->participantRoles === null )
        {
            $this->participantRoles = new ParticipantRoleCollection();

            $role = new ParticipantRole();
            $role->id = eZCollaborationItemParticipantLink::ROLE_STANDARD;
            $role->identifier = ParticipantRole::ROLE_STANDARD;
            $role->name = ezpI18n::tr( 'sensor/role_name', 'Standard' );
            $this->participantRoles->addParticipantRole( $role );

            $role = new ParticipantRole();
            $role->id = eZCollaborationItemParticipantLink::ROLE_OBSERVER;
            $role->identifier = ParticipantRole::ROLE_OBSERVER;
            $role->name = ezpI18n::tr( 'sensor/role_name', 'Osservatore' );
            $this->participantRoles->addParticipantRole( $role );

            $role = new ParticipantRole();
            $role->id = eZCollaborationItemParticipantLink::ROLE_OWNER;
            $role->identifier = ParticipantRole::ROLE_OWNER;
            $role->name = ezpI18n::tr( 'sensor/role_name', 'In carico a' );
            $this->participantRoles->addParticipantRole( $role );

            $role = new ParticipantRole();
            $role->id = eZCollaborationItemParticipantLink::ROLE_APPROVER;
            $role->identifier = ParticipantRole::ROLE_APPROVER;
            $role->name = ezpI18n::tr( 'sensor/role_name', 'Riferimento per il cittadino' );
            $this->participantRoles->addParticipantRole( $role );

            $role = new ParticipantRole();
            $role->id = eZCollaborationItemParticipantLink::ROLE_AUTHOR;
            $role->identifier = ParticipantRole::ROLE_AUTHOR;
            $role->name = ezpI18n::tr( 'sensor/role_name', 'Autore' );
            $this->participantRoles->addParticipantRole( $role );
        }

        return $this->participantRoles;
    }

    public function loadPostParticipantById( Post $post, $id )
    {
        return $this->internalLoadPostParticipants( $post )->getParticipantById( $id );
    }

    /**
     * @param Post $post
     * @param $role
     *
     * @return ParticipantCollection
     */
    public function loadPostParticipantsByRole( Post $post, $role )
    {
        return $this->internalLoadPostParticipants( $post )->getParticipantsByRole( $role );
    }

    /**
     * @param Post $post
     *
     * @return ParticipantCollection
     */
    public function loadPostParticipants( Post $post )
    {
        return $this->internalLoadPostParticipants( $post );
    }

    public function addPostParticipant( Post $post, Participant $participant )
    {
        // TODO: Implement addPostParticipant() method.
    }

    public function trashPostParticipant( Post $post, Participant $participant )
    {
        // TODO: Implement trashPostParticipant() method.
    }

    public function restorePostParticipant( Post $post, Participant $participant )
    {
        // TODO: Implement restorePostParticipant() method.
    }

    protected function internalLoadPostParticipants( Post $post )
    {
        $postInternalId = $post->internalId;
        if ( !isset( $this->participantsByPost[$postInternalId] ) )
        {
            $this->participantsByPost[$postInternalId] = new ParticipantCollection();

            /** @var eZCollaborationItemParticipantLink[] $participantLinks */
            $participantLinks = eZCollaborationItemParticipantLink::fetchParticipantList(
                array(
                    'item_id' => $postInternalId,
                    'limit' => 1000 // avoid ez cache
                )
            );
            $participantIdList = array();
            foreach ( $participantLinks as $participantLink )
            {
                $participantIdList[] = $participantLink->attribute( 'participant_id' );
            }
            /** @var eZContentObject[] $objects */
            $objects = eZContentObject::fetchIDArray( $participantIdList );

            foreach ( $participantLinks as $participantLink )
            {
                $id = $participantLink->attribute( 'participant_id' );
                $object = isset( $objects[$id] ) ? $objects[$id] : null;
                $participant = $this->internalLoadParticipant(
                    $participantLink,
                    $object
                );

                $this->participantsByPost[$postInternalId]->addParticipant( $participant );
            }
        }

        return $this->participantsByPost[$postInternalId];
    }

    protected function internalLoadParticipant(
        eZCollaborationItemParticipantLink $participantLink,
        eZContentObject $contentObject = null
    )
    {
        $role = $this->loadParticipantRoleCollection()->getParticipantRoleById( $participantLink->attribute( 'participant_role' ) );
        $participant = new Participant();
        $participant->id = $participantLink->attribute( 'participant_id' );
        $participant->roleIdentifier = $role->identifier;
        $participant->roleName = $role->name;
        $participant->lastAccessDateTime = Utils::getDateTimeFromTimestamp(
            $participantLink->attribute( 'last_read' )
        );

        if ( $contentObject instanceof eZContentObject )
        {
            $participant->name = $contentObject->name(
                false,
                $this->repository->getCurrentLanguage()
            );
            if ( $participantLink->attribute( 'participant_type' ) == eZCollaborationItemParticipantLink::TYPE_USER )
            {
                $participant->addUser(
                    $this->repository->getUserService()->loadUser(
                        $contentObject->attribute( 'id' )
                    )
                );
            }
            elseif ( $participantLink->attribute( 'participant_type' ) == eZCollaborationItemParticipantLink::TYPE_USERGROUP )
            {
                /** @var \eZContentObjectTreeNode $child */
                foreach ( $contentObject->mainNode()->children() as $child )
                {
                    $participant->addUser(
                        $this->repository->getUserService()->loadUser(
                            $child->attribute( 'contentobject_id' )
                        )
                    );
                }
            }
        }

        return $participant;
    }

}