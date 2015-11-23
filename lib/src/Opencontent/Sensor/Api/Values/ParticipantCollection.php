<?php

namespace OpenContent\Sensor\Api\Values;

use OpenContent\Sensor\Api\Exportable;
use Traversable;

class ParticipantCollection extends Exportable implements \IteratorAggregate
{

    /**
     * @var Participant[]
     */
    public $participants;

    /**
     * @param $id
     *
     * @return Participant
     */
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