<?php

namespace OpenContent\Sensor\Legacy\Values;

use ArrayAccess;

class Settings implements ArrayAccess
{

    private $container = array();

    public function __construct( array $data )
    {
        $this->container = $data;
    }

    public function offsetExists( $offset )
    {
        return isset( $this->container[$offset] );
    }

    public function offsetGet( $offset )
    {
        return isset( $this->container[$offset] ) ? $this->container[$offset] : null;
    }

    public function offsetSet( $offset, $value )
    {
        if ( is_null( $offset ) )
        {
            $this->container[] = $value;
        }
        else
        {
            $this->container[$offset] = $value;
        }
    }

    public function offsetUnset( $offset )
    {
        unset( $this->container[$offset] );
    }
}