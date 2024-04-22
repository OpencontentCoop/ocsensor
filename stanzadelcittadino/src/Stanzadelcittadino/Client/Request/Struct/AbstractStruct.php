<?php

namespace Opencontent\Stanzadelcittadino\Client\Request\Struct;

class AbstractStruct
{
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data)
    {
        $instance = new static;
        foreach ($data as $key => $value) {
            if (property_exists(get_called_class(), $key)) {
                $instance->{$key} = $value;
            }
        }
        return $instance;
    }
}