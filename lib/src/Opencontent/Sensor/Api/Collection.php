<?php

namespace OpenContent\Sensor\Api;

abstract class Collection extends Exportable implements \IteratorAggregate
{
    public function getIterator()
    {
        return new \ArrayIterator( $this->toArray() );
    }

    public function first()
    {
        $items = $this->toArray();
        return array_shift( $items );
    }

    public function last()
    {
        $items = $this->toArray();
        return array_pop( $items );
    }

    public static function fromCollection( Collection $collection )
    {
        $newCollection = new static();
        $newCollection->fromArray( $collection->toArray() );
        return $newCollection;
    }

    abstract protected function toArray();

    abstract protected function fromArray(array $data);

}