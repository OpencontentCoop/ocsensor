<?php

use Opencontent\Sensor\Api\Exception\UnauthorizedException;

class SensorConnectorController extends SensorOpenApiController implements SensorOpenApiControllerInterface
{
    public function doEndpoint()
    {
        try {
            $rawRequest = $this->request->raw;
            $connectorIdentifier = $rawRequest['HTTP_X_SENSOR_CONNECTOR'];
            $configuration = SensorConnectorConfigurationFactory::instance()->getConfiguration($connectorIdentifier);
            if (!$configuration) {
                throw new UnauthorizedException();
            }

            $controller = new SensorConnector($this->openApiTools, $this);

            $result = $controller->run($configuration);

        }catch (Exception $e){
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }
}