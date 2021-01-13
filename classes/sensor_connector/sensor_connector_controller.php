<?php

use Opencontent\Sensor\Api\Exception\UnauthorizedException;

class SensorConnectorController extends SensorOpenApiController implements SensorOpenApiControllerInterface
{
    public function doEndpoint()
    {
        try {
            $rawRequest = $this->request->raw;
            if (!isset($rawRequest['HTTP_X_WEBHOOK_TRIGGER'])) {
                throw new BadMethodCallException("Missing HTTP_X_WEBHOOK_TRIGGER header");
            }

            $triggerIdentifier = $rawRequest['HTTP_X_WEBHOOK_TRIGGER'];
            if (!in_array($triggerIdentifier, [SensorConnectorSenderTrigger::IDENTIFIER, SensorConnectorReceiverTrigger::IDENTIFIER])){
                throw new BadMethodCallException("Invalid HTTP_X_WEBHOOK_TRIGGER header");
            }

            $trigger = $triggerIdentifier == SensorConnectorSenderTrigger::IDENTIFIER ? SensorConnectorReceiverTrigger::IDENTIFIER : SensorConnectorSenderTrigger::IDENTIFIER;
            $signature = $rawRequest['HTTP_SIGNATURE'];
            $configuration = SensorConnectorConfigurationFactory::instance()
                ->getConfigurationByTriggerAndSignature($trigger, $signature, $this->getPayload());

            if (!$configuration instanceof SensorConnectorConfiguration) {
                throw new UnauthorizedException();
            }

            if (isset($rawRequest['HTTP_X_WEBHOOK_REMOTE_USER'])) {
                $configuration->setUserName($rawRequest['HTTP_X_WEBHOOK_REMOTE_USER']);
            }

            $controller = new SensorConnector($this->openApiTools, $this);
            $result = $controller->run($configuration);
            if (isset($result->variables['message']) && !empty($result->variables['message'])){
                header('X-Sensor-Connector-Message: ' . $result->variables['message']);
            }

        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }
}