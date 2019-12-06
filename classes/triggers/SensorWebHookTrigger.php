<?php

class SensorWebHookTrigger implements OCWebHookTriggerInterface
{
    protected $identifier;

    protected $name;

    public function __construct($identifier, $name)
    {
        $this->identifier = $identifier;
        $this->name = $name;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return "Viene scatenato quando accade un evento di tipo '" . $this->getName() . "'. Il payload è un oggetto json API Sensor Post";
    }

    public function canBeEnabled()
    {
        return true;
    }

    public function useFilter()
    {
        return false;
    }

    public function isValidPayload($payload, $filters)
    {
        return true;
    }

}