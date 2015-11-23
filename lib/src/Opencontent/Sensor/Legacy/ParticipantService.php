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
use OpenContent\Sensor\Api\Values\Participant\ReporterCollection;
use OpenContent\Sensor\Api\Values\ParticipantCollection;
use OpenContent\Sensor\Api\Values\PermissionCollection;
use OpenContent\Sensor\Api\Values\Post;
use OpenContent\Sensor\Core\ParticipantService as ParticipantServiceBase;
use eZUser;
use eZContentObject;
use eZCollaborationItemParticipantLink;
use eZCollaborationItemStatus;
use ezpI18n;

class ParticipantService extends ParticipantServiceBase
{

    /**
     * @var ParticipantCollection[]
     */
    protected $participantsByPost = array();

    protected function internalLoadPostParticipants( Post $post )
    {
        $postInternalId = $post->internalId;
        if ( !isset( $this->participantsByPost[$postInternalId] ) )
        {
            $this->participantsByPost[$postInternalId] = array(
                'all' => new ParticipantCollection(),
                'approvers' => new ApproverCollection(),
                'owners' => new OwnerCollection(),
                'observers' => new ObserverCollection(),
                'reporters' => new ReporterCollection()
            );
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

            /** @var eZUser[] $users */
            $users = eZUser::fetchObjectList(
                eZUser::definition(),
                null,
                array( 'contentobject_id' => array( $participantIdList ) )
            );

            /** @var eZCollaborationItemStatus[] $userStatuses */
            $userStatuses = eZCollaborationItemStatus::fetchObjectList(
                eZCollaborationItemStatus::definition(),
                null,
                array(
                    'collaboration_id' => $postInternalId,
                    'user_id' => array( $participantIdList )
                )
            );

            foreach( $participantLinks as $participantLink )
            {
                $participant = new Participant();
                $participant->id = $participantLink->attribute( 'participant_id' );
                $participant->roleIdentifier = $participantLink->attribute( 'participant_role' );
                $participant->roleName = $this->getParticipantRoleName( $participantLink->attribute( 'participant_role' ) );

                $participant->lastAccessDateTime = $participantLink->attribute( 'created' );
                foreach( $userStatuses as $userStatus )
                {
                    if ( $userStatus->attribute( 'user_id' ) ==  $participant->id )
                        $participant->lastAccessDateTime = Utils::getDateTimeFromTimestamp( $userStatus->attribute( 'last_read' ) );
                }

                if ( isset( $objects[$participant->id] ) )
                {
                    $participant->name = $objects[$participant->id]->attribute( 'name' );
                }

                foreach( $users as $user )
                {
                    if ( $user->attribute( 'contentobject_id' ) == $participant->id )
                    {
                        $participant->email = $user->Email;
                    }
                }

                $this->participantsByPost[$postInternalId]['all']->addParticipant( $participant );
                if ( $participant->roleIdentifier == eZCollaborationItemParticipantLink::ROLE_APPROVER )
                    $this->participantsByPost[$postInternalId]['approvers']->addParticipant( $participant );
                elseif ( $participant->roleIdentifier == eZCollaborationItemParticipantLink::ROLE_OWNER )
                    $this->participantsByPost[$postInternalId]['owners']->addParticipant( $participant );
                elseif ( $participant->roleIdentifier == eZCollaborationItemParticipantLink::ROLE_OBSERVER )
                    $this->participantsByPost[$postInternalId]['observers']->addParticipant( $participant );
                elseif ( $participant->roleIdentifier == eZCollaborationItemParticipantLink::ROLE_AUTHOR )
                    $this->participantsByPost[$postInternalId]['reporter'] = $participant;
            }
        }
        return $this->participantsByPost[$postInternalId];
    }

    public function loadPostParticipants( Post $post )
    {
        $participants = $this->internalLoadPostParticipants( $post );
        return $participants['all'];
    }

    public function loadPostApprovers( Post $post )
    {
        $participants = $this->internalLoadPostParticipants( $post );
        return $participants['approvers'];
    }

    public function loadPostOwners( Post $post )
    {
        $participants = $this->internalLoadPostParticipants( $post );
        return $participants['owners'];
    }

    public function loadPostObservers( Post $post )
    {
        $participants = $this->internalLoadPostParticipants( $post );
        return $participants['observers'];
    }

    public function loadPostReporter( Post $post )
    {
        $participants = $this->internalLoadPostParticipants( $post );
        return $participants['reporter'];
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