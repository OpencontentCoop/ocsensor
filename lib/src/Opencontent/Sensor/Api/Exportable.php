<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 23/11/15
 * Time: 23:24
 */

namespace OpenContent\Sensor\Api;


abstract class Exportable
{
    public static function __set_state( $array )
    {
        $object = new static();
        foreach( $array as $key => $value )
        {
            $object->{$key} = $value;
        }
        return $object;
    }
}