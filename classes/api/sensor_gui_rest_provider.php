<?php

class SensorGuiApiProvider implements ezpRestProviderInterface
{
    public function getRoutes()
    {
        $routes = array(

            'sensorGuiApiSettings' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/settings',
                'SensorGuiApiController',
                'settings',
                array(),
                'http-get'
            ), 1),

            'sensorGuiApiPostsLoad' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/posts',
                'SensorGuiApiController',
                'loadPosts',
                array('limit' => 10, 'cursor' => '*'),
                'http-get'
            ), 1),

            'sensorGuiApiPostLoad' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/posts/:Id',
                'SensorGuiApiController',
                'loadPostById',
                array(),
                'http-get'
            ), 1),

            'sensorGuiApiPostSearch' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/posts/search',
                'SensorGuiApiController',
                'postSearch',
                array('query' => ''),
                'http-get'
            ), 1),

            'sensorGuiApiPostCreate' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/posts',
                'SensorGuiApiController',
                'createPost',
                array(),
                'http-post'
            ), 1),

            'sensorGuiApiPostUpdate' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/posts/:Id',
                'SensorGuiApiController',
                'updatePost',
                array(),
                'http-put'
            ), 1),

            'sensorGuiApiPostDelete' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/posts/:Id',
                'SensorGuiApiController',
                'deletePost',
                array(),
                'http-delete'
            ), 1),

            'sensorGuiApiUsersLoad' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/users',
                'SensorGuiApiController',
                'loadUsers',
                array('q' => '', 'limit' => 10, 'cursor' => '*'),
                'http-get'
            ), 1),

            'sensorGuiApiUserLoad' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/users/:UserId',
                'SensorGuiApiController',
                'loadUserById',
                array(),
                'http-get'
            ), 1),

            'sensorGuiApiUserPostCapabilities' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/users/:UserId/capabilities/:Id',
                'SensorGuiApiController',
                'loadUserPostCapabilities',
                array(),
                'http-get'
            ), 1),

            'sensorGuiApiOperatorsLoad' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/operators',
                'SensorGuiApiController',
                'loadOperators',
                array('query' => '', 'limit' => 10, 'cursor' => '*'),
                'http-get'
            ), 1),

            'sensorGuiApiOperatorLoad' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/operators/:OperatorId',
                'SensorGuiApiController',
                'loadOperator',
                array('query' => '', 'limit' => 10, 'cursor' => '*'),
                'http-get'
            ), 1),

            'sensorGuiApiGroupsLoad' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/groups',
                'SensorGuiApiController',
                'loadGroups',
                array('query' => '', 'limit' => 10, 'cursor' => '*'),
                'http-get'
            ), 1),

            'sensorGuiApiOperatorsByGroupLoad' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/groups/:GroupId',
                'SensorGuiApiController',
                'loadOperatorsByGroup',
                array(),
                'http-get'
            ), 1),

            'sensorGuiApiOperatorsAndGroupsLoad' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/operators_and_groups',
                'SensorGuiApiController',
                'loadOperatorsAndGroups',
                array('query' => '', 'limit' => 50, 'cursor' => '*'),
                'http-get'
            ), 1),

            'sensorGuiApiPostActions' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/actions/:Id/:Action',
                'SensorGuiApiController',
                'postAction',
                array(),
                'http-post'
            ), 1),

            'sensorGuiApiPostUpload' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/upload/:Id/:Action',
                'SensorGuiApiController',
                'postUpload',
                array(),
                'http-post'
            ), 1),

            'sensorGuiApiCategoryTree' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/category_tree',
                'SensorGuiApiController',
                'loadCategoryTree',
                array(),
                'http-get'
            ), 1),

            'sensorGuiApiAreaTree' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/area_tree',
                'SensorGuiApiController',
                'loadAreaTree',
                array(),
                'http-get'
            ), 1),

            'sensorGuiApiStat' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/stat/:Identifier',
                'SensorGuiApiController',
                'loadStat',
                array(),
                'http-get'
            ), 1),

            'sensorGuiApiTempUpload' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/upload-temp',
                'SensorGuiApiController',
                'tempUpload',
                array(),
                'http-post'
            ), 1),
        );
        return $routes;
    }

    public function getViewController()
    {
        return new SensorApiViewController();
    }

}
