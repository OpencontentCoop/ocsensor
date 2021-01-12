<?php

use Opencontent\Sensor\Api\Exception\UnauthorizedException;

class SensorConnectorController extends SensorOpenApiController implements SensorOpenApiControllerInterface
{
    public function doEndpoint()
    {
        try {
            $rawRequest = $this->request->raw;

            if (!isset($rawRequest['HTTP_X_WEBHOOK_TRIGGER'])
                || $rawRequest['HTTP_X_WEBHOOK_TRIGGER'] !== 'sensor_connector'){
                throw new BadMethodCallException("Missing or invalid HTTP_X_WEBHOOK_TRIGGER header");
            }

            $signature = $rawRequest['HTTP_SIGNATURE'];
            $configuration = SensorConnectorConfigurationFactory::instance()->getConfigurationBySignature($signature, $this->getPayload());
            if (!$configuration instanceof SensorConnectorConfiguration) {
                throw new UnauthorizedException();
            }

            $user = $configuration->getUser();
            if ($user instanceof eZUser){
                eZUser::setCurrentlyLoggedInUser($user, $user->id());
            }

            $controller = new SensorConnector($this->openApiTools, $this);

            $result = $controller->run($configuration);

        }catch (Exception $e){
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }
}