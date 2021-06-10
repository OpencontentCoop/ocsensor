<?php

class SensorDailyReport implements OCCustomSearchableObjectInterface, JsonSerializable
{
    private $attributes = array();

    public function __construct(array $data)
    {
        foreach($data as $key => $value){
            $this->attributes[$key] = $value;
        }
    }

    public function getGuid()
    {
        $parts = explode('T', $this->attributes['date']);
        return 'day-' . $parts[0];
    }

    public function getFieldValue(OCCustomSearchableFieldInterface $field)
    {
        if (isset($this->attributes[$field->getName()])){
            return $this->attributes[$field->getName()];
        }

        return in_array($field->getType(), ['int', 'sfloat']) ? -1 : null;
    }

    public function toArray()
    {
        return $this->attributes;
    }

    public function jsonSerialize()
    {
        return $this->attributes;
    }

    public static function fromArray($array)
    {
        return new SensorDailyReport($array);
    }


    // deprecations

    public static function getFields()
    {
        //ignore using OCCustomSearchableRepositoryObjectCreatorInterface
        return [];
    }
}