<?php

namespace OpenContent\Sensor\Core\PermissionDefinitions;

use OpenContent\Sensor\Api\Values\Participant;
use OpenContent\Sensor\Api\Values\Post;
use OpenContent\Sensor\Api\Values\User;
use OpenContent\Sensor\Api\Values\ParticipantRole;

class CanReopen extends UserIs
{
    public $identifier = 'can_reopen';

    public function userHasPermission( User $user, Post $post )
    {
        return $this->userIs( ParticipantRole::ROLE_APPROVER, $user, $post )
               && $post->workflowStatus->is( Post\WorkflowStatus::CLOSED );
    }
}