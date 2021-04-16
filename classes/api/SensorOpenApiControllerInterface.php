<?php

interface SensorOpenApiControllerInterface
{
    public function getBaseUri();

    public function getPayload();

    /**
     * @return ezcMvcRequest
     */
    public function getRequest();
}