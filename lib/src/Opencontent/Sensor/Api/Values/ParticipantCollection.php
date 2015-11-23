<?php

namespace OpenContent\Sensor\Api\Values;

use Traversable;

class ParticipantCollection implements \IteratorAggregate
{

    /**
     * @var Participant
     */
    protected $participants;

    public function getParticipantById( $id )
    {
        return isset( $this->participants[$id] ) ? $this->participants[$id] : false;
    }

    public function addParticipant( Participant $participant )
    {
        $this->participants[$participant->id] = $participant;
    }

    public function getIterator()
    {
        return new \ArrayIterator( $this->participants );
    }
}