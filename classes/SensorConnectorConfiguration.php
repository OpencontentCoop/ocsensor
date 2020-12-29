<?php


class SensorConnectorConfiguration
{
    public $identifier;

    public function __construct(array $data)
    {
        foreach ($data as $key => $value){
            $this->{$key} = $value;
        }
    }

    public function generateRemoteId($id)
    {
        return $this->identifier . '_' . $id;
    }

}