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

            'sensorGuiApiUsersAsOrganizationsLoad' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/users_as_organizations',
                'SensorGuiApiController',
                'loadUsersAsOrganizations',
                array('q' => '', 'limit' => 10, 'cursor' => '*'),
                'http-get'
            ), 1),

            'sensorGuiApiUserLocaleLoad' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/users/current/locale',
                'SensorGuiApiController',
                'loadCurrentUserLocale',
                array(),
                'http-get'
            ), 1),

            'sensorGuiApiUserLocalePut' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/users/current/locale/:LanguageCode',
                'SensorGuiApiController',
                'postCurrentUserLocale',
                array(),
                'http-post'
            ), 1),

            'sensorGuiApiUserLoad' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/users/:UserId',
                'SensorGuiApiController',
                'loadUserById',
                array(),
                'http-get'
            ), 1),

            'sensorGuiApiUserGroupLoad' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/user-groups/:userGroupId',
                'SensorGuiApiController',
                'loadUserGroupById',
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
                '/upload-temp/:Identifier',
                'SensorGuiApiController',
                'tempUpload',
                array(),
                'http-post'
            ), 1),

            'sensorGuiApiTodolist' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/inbox/:Identifier',
                'SensorGuiApiController',
                'loadInbox',
                array('page' => 1, 'limit' => 10),
                'http-get'
            ), 1),

            'sensorGuiApiBookmarkedPostsLoad' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/specials',
                'SensorGuiApiController',
                'loadSpecialIdList',
                array(),
                'http-get'
            ), 1),

            'sensorGuiApiBookmark' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/special/:Id/:Enable',
                'SensorGuiApiController',
                'loadSpecial',
                array(),
                'http-post'
            ), 1),

            'sensorGuiApiTaggedSpecial' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/tagged-important/:Id/:Enable',
                'SensorGuiApiController',
                'setAsTaggedImportant',
                array(),
                'http-post'
            ), 1),

            'sensorGuiApiScenarioSearch' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/scenarios',
                'SensorGuiApiController',
                'scenarioSearch',
                array(),
                'http-get'
            ), 1),

            'sensorGuiApiScenarioCreate' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/scenarios',
                'SensorGuiApiController',
                'createScenario',
                array(),
                'http-post'
            ), 1),

            'sensorGuiApiScenarioEdit' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/scenarios/:Id',
                'SensorGuiApiController',
                'editScenario',
                array(),
                'http-put'
            ), 1),

            'sensorGuiApiArea' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/areas/:Id',
                'SensorGuiApiController',
                'loadArea',
                array(),
                'http-get'
            ), 1),

            'sensorGuiApiAreaDisabledCategories' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/areas/:Id/disabled_categories',
                'SensorGuiApiController',
                'postAreaDisabledCategories',
                array(),
                'http-post'
            ), 1),

            'sensorGuiApiPostLoadWithCapabilities' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/posts_and_capabilities/:Id',
                'SensorGuiApiController',
                'loadPostByIdWithCapabilities',
                array(),
                'http-get'
            ), 1),

            'sensorGuiApiLoadDefaultArea' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/default_area',
                'SensorGuiApiController',
                'loadDefaultArea',
                array(),
                'http-get'
            ), 1),

            'sensorGuiApiPredictCategories' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/predict/:Id/categories',
                'SensorGuiApiController',
                'predictCategories',
                array(),
                'http-get'
            ), 1),

            'sensorGuiApiPredictFaqs' => new ezpRestVersionedRoute(new SensorApiRailsRoute(
                '/predict/faqs',
                'SensorGuiApiController',
                'predictFaqs',
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
