<?php

use Opencontent\Opendata\Api\ClassRepository;

/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$postId = $Params['ID'];
$repository = OpenPaSensorRepository::instance();

if (!is_numeric($postId)) {

    $tpl->setVariable('areas', $repository->getAreasTree());
    $tpl->setVariable('categories', $repository->getCategoriesTree());
    $Result = array();
    $Result['persistent_variable'] = $tpl->variable('persistent_variable');
    $Result['content'] = $tpl->fetch('design:sensor_api_gui/posts.tpl');
    $Result['node_id'] = $repository->getPostRootNode()->attribute('node_id');

    $contentInfoArray = array('url_alias' => 'sensor/posts');
    $contentInfoArray['persistent_variable'] = false;
    if ($tpl->variable('persistent_variable') !== false) {
        $contentInfoArray['persistent_variable'] = $tpl->variable('persistent_variable');
    }
    $Result['content_info'] = $contentInfoArray;
    $Result['path'] = array();

    return $Result;

} else {

    try {
        $post = $repository->getSearchService()->searchPost($postId);

        $readAction = new \Opencontent\Sensor\Api\Action\Action();
        $readAction->identifier = 'read';
        $repository->getActionService()->runAction($readAction, $post);

        $tpl->setVariable('post_id', (int)$postId);

        $tpl->setVariable('areas', json_encode($repository->getAreasTree()));
        $tpl->setVariable('categories', json_encode($repository->getCategoriesTree()));
        $tpl->setVariable('operators', json_encode($repository->getOperatorsTree()));
        $tpl->setVariable('groups', json_encode($repository->getGroupsTree()));
        $tpl->setVariable('settings', json_encode($repository->getSensorSettings()));

        $classRepository = new ClassRepository();
        $tpl->setVariable('sensor_post', json_encode($classRepository->load($repository->getPostContentClassIdentifier())));

        $Result = array();
        $Result['persistent_variable'] = $tpl->variable('persistent_variable');

        $layoutVersion = 1;
        if (eZINI::instance('ocsensor.ini')->hasVariable('SensorConfig', 'PostLayoutVersion')) {
            $layoutVersion = eZINI::instance('ocsensor.ini')->variable('SensorConfig', 'PostLayoutVersion');
        }
        $layoutPreference = eZPreferences::value('sensor_post_layout');
        if ($layoutPreference){
            $layoutVersion = (int)$layoutPreference;
        }

        $Result['content'] = $tpl->fetch('design:sensor_api_gui/posts/v'. $layoutVersion . '/post.tpl');
        $Result['node_id'] = 0;

        $contentInfoArray = array('url_alias' => 'sensor/post/' . $postId);
        $contentInfoArray['persistent_variable'] = false;
        if ($tpl->variable('persistent_variable') !== false) {
            $contentInfoArray['persistent_variable'] = $tpl->variable('persistent_variable');
        }
        $Result['content_info'] = $contentInfoArray;
        $Result['path'] = array();

        return $Result;

    } catch (\Opencontent\Sensor\Api\Exception\NotFoundException $e) {
        eZDebug::writeError($e->getMessage(), __FILE__);
        return $module->handleError(eZError::KERNEL_NOT_FOUND, 'kernel', ['error' => $e->getMessage()]);

    } catch (\Opencontent\Sensor\Api\Exception\BaseException $e) {
        eZDebug::writeError($e->getMessage(), __FILE__);
        return $module->handleError(eZError::KERNEL_ACCESS_DENIED, 'kernel', ['error' => $e->getMessage()]);
    }

}
