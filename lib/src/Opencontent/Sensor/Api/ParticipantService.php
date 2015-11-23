<?php

namespace OpenContent\Sensor\Api;

use OpenContent\Sensor\Api\Values\Post;
use OpenContent\Sensor\Api\Values\Participant;
use OpenContent\Sensor\Api\Values\ParticipantCollection;
use OpenContent\Sensor\Api\Values\Participant\ApproverCollection;
use OpenContent\Sensor\Api\Values\Participant\OwnerCollection;
use OpenContent\Sensor\Api\Values\Participant\ObserverCollection;
use OpenContent\Sensor\Api\Values\Participant\ReporterCollection;

interface ParticipantService
{
    /**
     * @param Post $post
     *
     * @return ParticipantCollection
     */
    public function loadPostParticipants( Post $post );

    /**
     * @param Post $post
     *
     * @return ApproverCollection
     */
    public function loadPostApprovers( Post $post );

    /**
     * @param Post $post
     *
     * @return OwnerCollection
     */
    public function loadPostOwners( Post $post );

    /**
     * @param Post $post
     *
     * @return ObserverCollection
     */
    public function loadPostObservers( Post $post );

    /**
     * @param Post $post
     *
     * @return Participant
     */
    public function loadPostReporter( Post $post );

    /**
     * @param Post $post
     * @param Participant $participant
     *
     */
    public function addPostParticipant( Post $post, Participant $participant );

    /**
     * @param Post $post
     * @param Participant $participant
     *
     */
    public function trashPostParticipant( Post $post, Participant $participant );

    /**
     * @param Post $post
     * @param Participant $participant
     *
     */
    public function restorePostParticipant( Post $post, Participant $participant );
}