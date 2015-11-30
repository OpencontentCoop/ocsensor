<?php

namespace OpenContent\Sensor\Api;

use OpenContent\Sensor\Api\Values\Post;
use OpenContent\Sensor\Api\Values\Participant;
use OpenContent\Sensor\Api\Values\ParticipantCollection;
use OpenContent\Sensor\Api\Values\Participant\ApproverCollection;
use OpenContent\Sensor\Api\Values\Participant\OwnerCollection;
use OpenContent\Sensor\Api\Values\Participant\ObserverCollection;
use OpenContent\Sensor\Api\Values\Participant\ReporterCollection;
use OpenContent\Sensor\Api\Values\ParticipantRoleCollection;

interface ParticipantService
{
    /**
     * @return ParticipantRoleCollection
     */
    public function loadParticipantRoleCollection();

    /**
     * @param Post $post
     * @param $id
     *
     * @return Participant
     */
    public function loadPostParticipantById( Post $post, $id );

    /**
     * @param Post $post
     * @param $role
     *
     * @return ParticipantCollection
     */
    public function loadPostParticipantsByRole( Post $post, $role );

    /**
     * @param Post $post
     *
     * @return ParticipantCollection
     */
    public function loadPostParticipants( Post $post );

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