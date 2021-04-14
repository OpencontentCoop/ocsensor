<?php

class SensorOpenApiProvider implements ezpRestProviderInterface
{
    public function getRoutes()
    {
        $routes = [

            // api che rispondono alla maniera del vecchio SensorCivico, utilizzate solo dal Comune di Trento
            'sensorCompatApiPostUpdate' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/edit',
                'SensorApiCompatController',
                'compatUpdatePost',
                [],
                'http-post'
            ), 0),

            'sensorOpenApi' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/',
                'SensorOpenApiController',
                'endpoint',
                [],
                'http-get'
            ), 1),
        ];

        if (SensorJwtManager::instance()->isJwtAuthEnabled()){
            $routes['sensorOpenApiAuth'] = new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/auth',
                'SensorOpenApiController',
                'auth',
                [],
                'http-post'
            ), 1);
        }

        $repository = OpenPaSensorRepository::instance();
        $openApiTools = new \Opencontent\Sensor\OpenApi($repository);
        $schema = $openApiTools->loadSchema();

        foreach ($schema['paths'] as $pattern => $path) {
            foreach ($path as $method => $definition) {

                $operationId = $definition['operationId'];
                $defaultValues = ['operationId' => $operationId];

                if (isset($definition['parameters'])) {
                    foreach ($definition['parameters'] as $parameter) {
                        if ($parameter['in'] == 'query') {
                            $defaultValues[$parameter['name']] = isset($parameter['schema']['default']) ? $parameter['schema']['default'] : null;
                        } elseif ($parameter['in'] == 'path') {
                            $pattern = str_replace('{' . $parameter['name'] . '}', ':' . $parameter['name'], $pattern);
                        }
                    }
                }
                $routeId = 'sensorOpenApi' . ucfirst($operationId);
                if (!isset($routes[$routeId])) {
                    $routes[$routeId] = new ezpRestVersionedRoute(new SensorApiRailsRoute(
                        $pattern,
                        'SensorOpenApiController',
                        'action',
                        $defaultValues,
                        'http-' . $method
                    ), 1);
                }
            }
        }

        $routes[] = new ezpRestVersionedRoute(new SensorApiRailsRoute(
            '/file/:attributeIdentifier/:fileName/:originalFileName',
            'SensorOpenApiController',
            'getFile',
            [],
            'http-get'
        ), 1);

        return $routes;
    }

    public function getViewController()
    {
        return new SensorApiViewController();
    }

}
