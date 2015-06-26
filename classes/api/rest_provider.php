<?php


class SensorApiProvider implements ezpRestProviderInterface
{
    public function getRoutes()
    {
        $routes = array(

            // api che rispondono alla maniera del vecchio SensorCivico, utilizzate dal Comune di Trento
            'sensorCompatApiPostUpdate' => new ezpRestVersionedRoute( new SensorApiRailsRoute( '/edit', 'SensorApiController', 'compatUpdatePost', array(), 'http-post' ), 0 ),

            // nuove api @todo
            'sensorApiPostLoad' => new ezpRestVersionedRoute( new SensorApiRailsRoute( '/post/:Id', 'SensorApiController', 'loadPost', array(), 'http-get' ), 1 ),
            'sensorApiPostCreate' => new ezpRestVersionedRoute( new SensorApiRailsRoute( '/post', 'SensorApiController', 'createPost', array(), 'http-post' ), 1 ),
            'sensorApiPostUpdate' => new ezpRestVersionedRoute( new SensorApiRailsRoute( '/post/:Id', 'SensorApiController', 'updatePost', array(), 'http-put' ), 1 ),

            'sensorApiPostDetail'    => new ezpRestVersionedRoute( new SensorApiRailsRoute( '/post/:Id/:Detail', 'SensorApiController', 'viewDetail', array(), 'http-get' ), 1 ),
        );
        return $routes;
    }

    public function getViewController()
    {
        return new SensorApiViewController();
    }

}
