<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 23/11/15
 * Time: 13:54
 */

namespace OpenContent\Sensor\Core;

use OpenContent\Sensor\Api\ParticipantService as ParticipantServiceInterface;
use OpenContent\Sensor\Api\Values\Participant;
use OpenContent\Sensor\Api\Values\Participant\ApproverCollection;
use OpenContent\Sensor\Api\Values\Participant\ObserverCollection;
use OpenContent\Sensor\Api\Values\Participant\OwnerCollection;
use OpenContent\Sensor\Api\Values\Participant\ReporterCollection;
use OpenContent\Sensor\Api\Values\ParticipantCollection;
use OpenContent\Sensor\Api\Values\Post;

class ParticipantService implements ParticipantServiceInterface
{

    public function loadPostAuthor( Post $post )
    {
        // TODO: Implement loadPostAuthor() method.
    }

    public function loadPostParticipants( Post $post )
    {
        // TODO: Implement loadPostParticipants() method.
    }

    public function loadPostApprovers( Post $post )
    {
        // TODO: Implement loadPostApprovers() method.
    }

    public function loadPostOwners( Post $post )
    {
        // TODO: Implement loadPostOwners() method.
    }

    public function loadPostObservers( Post $post )
    {
        // TODO: Implement loadPostObservers() method.
    }

    public function loadPostReporters( Post $post )
    {
        // TODO: Implement loadPostReporters() method.
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
}