<?php


namespace OpenContent\Sensor\Api\Values;

use OpenContent\Sensor\Api\Values\User;
use DateTime;

class Participant extends User
{
    public $roleIdentifier;

    public $roleName;

    /**
     * @var DateTime
     */
    public $lastAccessDateTime;
}