<?php

use Opencontent\Ocopendata\Forms\Connectors\AbstractBaseConnector;

class SensorConnectorConfigurationConnector extends AbstractBaseConnector
{
    protected function getData()
    {
        return [];
    }

    protected function getSchema()
    {
        // TODO: Implement getSchema() method.
    }

    protected function getOptions()
    {
        // TODO: Implement getOptions() method.
    }

    protected function getView()
    {
        // TODO: Implement getView() method.
    }

    protected function submit()
    {
        // TODO: Implement submit() method.
    }

    protected function upload()
    {
        throw new BadMethodCallException('Upload not allowed');
    }

}