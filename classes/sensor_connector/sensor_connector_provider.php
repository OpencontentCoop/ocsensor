<?php

class SensorConnectorProvider implements ezpRestProviderInterface
{
    public function getRoutes()
    {
        return [
            'sensorWebhookReceiverPost' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/',
                'SensorConnectorController',
                'endpoint',
                [],
                'http-post'
            ), 1),
        ];
    }

    public function getViewController()
    {
        return new SensorApiViewController();
    }
}