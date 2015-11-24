<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 23/11/15
 * Time: 21:33
 */

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

class ParticipantService extends ParticipantServiceBase
{

    /**
     * @var ParticipantCollection[]
     */
    protected $participantsByPost = array();

    /**
     * @var ParticipantCollection[]
     */
    protected $approversByPost = array();

    /**
     * @var ParticipantCollection[]
     */
    protected $ownersByPost = array();

    /**
     * @var ParticipantCollection[]
     */
    protected $observerByPost = array();

    /**
     * @var Participant[]
     */
    protected $reporterByPost = array();

    public function loadPostParticipantById( Post $post, $id )
    {
        $this->internalLoadPostParticipants( $post );
        return $this->participantsByPost[$post->internalId]->getParticipantById( $id );
    }

    public function loadPostParticipants( Post $post )
    {
        $this->internalLoadPostParticipants( $post );
        return $this->participantsByPost[$post->internalId];
    }

    public function loadPostApprovers( Post $post )
    {
        $this->internalLoadPostParticipants( $post );
        return $this->approversByPost[$post->internalId];
    }

    public function loadPostOwners( Post $post )
    {
        $this->internalLoadPostParticipants( $post );
        return $this->ownersByPost[$post->internalId];
    }

    public function loadPostObservers( Post $post )
    {
        $this->internalLoadPostParticipants( $post );
        return $this->observerByPost[$post->internalId];
    }

    public function loadPostReporter( Post $post )
    {
        $this->internalLoadPostParticipants( $post );
        return $this->reporterByPost[$post->internalId];
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
            $this->approversByPost[$postInternalId] = new ApproverCollection();
            $this->ownersByPost[$postInternalId] = new OwnerCollection();
            $this->observerByPost[$postInternalId] = new ObserverCollection();

            /** @var eZCollaborationItemParticipantLink[] $participantLinks */
            $participantLinks = eZCollaborationItemParticipantLink::fetchParticipantList(
                array(
                    'item_id' => $postInternalId,
                    'limit' => 1000 // avoid ez cache
                )
            );
            $participantIdList = array();
            foreach( $participantLinks as $participantLink )
            {
                $participantIdList[] = $participantLink->attribute( 'participant_id' );
            }
            /** @var eZContentObject[] $objects */
            $objects = eZContentObject::fetchIDArray( $participantIdList );

            foreach( $participantLinks as $participantLink )
            {
                $id = $participantLink->attribute( 'participant_id' );
                $object = isset( $objects[$id] ) ? $objects[$id] : null;
                $participant = $this->internalLoadParticipant(
                    $participantLink,
                    $object
                );

                $this->participantsByPost[$postInternalId]->addParticipant( $participant );

                if ( $participant->roleIdentifier == eZCollaborationItemParticipantLink::ROLE_APPROVER )
                    $this->approversByPost[$postInternalId]->addParticipant( $participant );

                elseif ( $participant->roleIdentifier == eZCollaborationItemParticipantLink::ROLE_OWNER )
                    $this->ownersByPost[$postInternalId]->addParticipant( $participant );

                elseif ( $participant->roleIdentifier == eZCollaborationItemParticipantLink::ROLE_OBSERVER )
                    $this->observerByPost[$postInternalId]->addParticipant( $participant );

                elseif ( $participant->roleIdentifier == eZCollaborationItemParticipantLink::ROLE_AUTHOR )
                    $this->reporterByPost[$postInternalId] = $participant;
            }
        }
        return $this->participantsByPost[$postInternalId];
    }

    protected function internalLoadParticipant(
        eZCollaborationItemParticipantLink $participantLink,
        eZContentObject $contentObject = null
    ) {
        $participant = new Participant();
        $participant->id = $participantLink->attribute( 'participant_id' );
        $participant->roleIdentifier = $participantLink->attribute( 'participant_role' );
        $participant->roleName = $this->getParticipantRoleName( $participantLink->attribute( 'participant_role' ) );
        $participant->lastAccessDateTime = Utils::getDateTimeFromTimestamp( $participantLink->attribute( 'last_read' ) );

        if ( $contentObject instanceof eZContentObject )
        {
            $participant->name = $contentObject->name( false, $this->repository->getCurrentLanguage() );
            if ( $participantLink->attribute( 'participant_type' ) == eZCollaborationItemParticipantLink::TYPE_USER )
            {
                $participant->addUser(
                    $this->repository->getUserService()->loadUser( $contentObject->attribute( 'id' ) )
                );
            }
            elseif ( $participantLink->attribute( 'participant_type' ) == eZCollaborationItemParticipantLink::TYPE_USERGROUP )
            {
                /** @var \eZContentObjectTreeNode $child */
                foreach( $contentObject->mainNode()->children() as $child )
                {
                    $participant->addUser(
                        $this->repository->getUserService()->loadUser( $child->attribute( 'contentobject_id' ) )
                    );
                }
            }
        }

        return $participant;
    }

    protected static function getParticipantRoleName( $roleID )
    {
        if ( empty( $GLOBALS['SensorParticipantRoleNameMap'] ) )
        {

            $GLOBALS['SensorParticipantRoleNameMap'] =
                array( eZCollaborationItemParticipantLink::ROLE_STANDARD => ezpI18n::tr( 'sensor/role_name', 'Standard' ),
                       eZCollaborationItemParticipantLink::ROLE_OBSERVER => ezpI18n::tr( 'sensor/role_name', 'Osservatore' ),
                       eZCollaborationItemParticipantLink::ROLE_OWNER => ezpI18n::tr( 'sensor/role_name', 'In carico a' ),
                       eZCollaborationItemParticipantLink::ROLE_APPROVER => ezpI18n::tr( 'sensor/role_name', 'Riferimento per il cittadino' ),
                       eZCollaborationItemParticipantLink::ROLE_AUTHOR => ezpI18n::tr( 'sensor/role_name', 'Autore' ) );
        }
        $roleNameMap = $GLOBALS['SensorParticipantRoleNameMap'];
        if ( isset( $roleNameMap[$roleID] ) )
        {
            return $roleNameMap[$roleID];
        }
        return null;
    }
}