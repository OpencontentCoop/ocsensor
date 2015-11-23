<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 23/11/15
 * Time: 09:14
 */

namespace OpenContent\Sensor\Api\Values;

use OpenContent\Sensor\Api\Values\Message;

class MessageCollection
{
    /**
     * @var int
     */
    public $count;

    /**
     * @var int
     */
    public $unreadCount;

    /**
     * @var Message
     */
    public $lastMessage;

    /**
     * @var Message[]
     */
    public $message;
}