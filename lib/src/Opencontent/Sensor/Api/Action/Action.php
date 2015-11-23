<?php

namespace OpenContent\Sensor\Api\Action;

use OpenContent\Sensor\Api\Action\ActionParameter;

class Action
{
    public $identifier;

    /**
     * @var ActionParameter[]
     */
    public $parameters;

    public function hasParameter( $name )
    {
        foreach( $this->parameters as $parameter )
        {
            if ( $parameter->identifier == $name && $parameter->value !== null )
            {
                return true;
            }
        }
        return false;
    }

    public function setParameter( $identifier, $value )
    {
        $newParameter = new ActionParameter();
        $newParameter->identifier = $identifier;
        $newParameter->value = $value;
        $this->parameters[] = $newParameter;
    }
}