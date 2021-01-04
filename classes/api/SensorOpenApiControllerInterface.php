<?php

interface SensorOpenApiControllerInterface
{
    public function getBaseUri();

    public function getPayload();

    public function getRequest();
}