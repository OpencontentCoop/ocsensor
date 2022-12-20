<?php

use Opencontent\Opendata\Api\ClassRepository;
use Opencontent\Sensor\Api\Exception\BaseException;
use Opencontent\Sensor\Api\Exception\NotFoundException;
use Opencontent\Sensor\Legacy\PermissionService;

/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$postId = $Params['ID'];
$repository = OpenPaSensorRepository::instance();

if (!is_numeric($postId)) {
    if (!OpenPaSensorRepository::isOnlyUserContext()){
        $access = eZUser::currentUser()->hasAccessTo('sensor', 'manage');
        $isOperator = $access['accessWord'] != 'no';

        $tpl->setVariable('areas', $repository->getAreasTree());
        $tpl->setVariable('categories', $repository->getCategoriesTree());
        $tpl->setVariable('types', $repository->getPostTypeService()->loadPostTypes());

        $operators = $isOperator ? $repository->getOperatorsTree() : [];
        $tpl->setVariable('operators', json_encode($operators));

        $groupTree = $repository->getGroupsTree();
        $tree = [];
        $groupTagCounter = [];
        foreach ($groupTree->attribute('children') as $groupTreeItem) {
            $groupTag = $groupTreeItem->attribute('group');
            $groupTreeItem = $groupTreeItem->toArray();
            if (!$groupTreeItem['is_enabled']) {
                continue;
            }
            if (empty($groupTag)) {
                $groupTreeItem['is_tag_group'] = false;
                $tree[$groupTreeItem['id']] = $groupTreeItem;
            } else {
                if (isset($groupTagCounter[$groupTag])) {
                    $groupTagId = $groupTagCounter[$groupTag];
                } else {
                    $groupTagId = $groupTagCounter[$groupTag] = count($groupTagCounter) + 1;
                }
                $groupTreeItem['level'] = 1;
                if (isset($tree[$groupTagId])) {
                    $tree[$groupTagId]['children'][] = $groupTreeItem;
                } else {
                    $tree[$groupTagId] = [
                        'name' => $groupTag,
                        'id' => $groupTagId,
                        'is_tag_group' => true,
                        'level' => 0,
                        'children' => [$groupTreeItem],
                        'children_count' => 0,
                    ];
                }
            }
        }
        foreach ($tree as $index => $group) {
            if ($group['is_tag_group']) {
                $tree[$index]['id'] = implode(',', array_column($group['children'], 'id'));
                $tree[$index]['children_count'] = count($group['children']);
                if ($tree[$index]['children_count'] < 2) {
                    $children = [];
                    foreach ($group['children'] as $child) {
                        $child['level'] = $child['level'] - 1;
                        $children[] = $child;
                    }
                    $tree[$index]['children'] = $children;
                }
            }
        }

        $groups = $isOperator ? $groupTree : [];
        $tpl->setVariable('groups', json_encode($groups));
        $groupedGroups = $isOperator ? ['children' => array_values($tree), 'children_count' => count($tree)] : [];
        $tpl->setVariable('grouped_groups', json_encode($groupedGroups));
    }
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

    $postId = (int)$postId;
    $contentObject = eZContentObject::fetch($postId);
    if (!$contentObject) {
        return $module->handleError(eZError::KERNEL_NOT_FOUND, 'kernel');
    }

    if (!OpenPaSensorRepository::isOnlyUserContext()){
        if (isset($Params['Offset']) && $Params['Offset'] === 'history') {
            if (PermissionService::isSuperAdmin($repository->getCurrentUser())) {
                eZSearch::addObject($contentObject, true);
                $postSerialized = $repository->getSearchService()->searchPost((int)$postId)->jsonSerialize();
                $tpl->setVariable('post', $postSerialized);

                $messages = [];
                foreach ($postSerialized['timelineItems'] as $message) {
                    $message['_type'] = 'system';
                    $messages[$message['id']] = $message;
                }
                foreach ($postSerialized['privateMessages'] as $message) {
                    $message['_type'] = 'private';
                    $messages[$message['id']] = $message;
                }
                foreach ($postSerialized['comments'] as $message) {
                    $message['_type'] = 'public';
                    $messages[$message['id']] = $message;
                }
                foreach ($postSerialized['responses'] as $message) {
                    $message['_type'] = 'response';
                    $messages[$message['id']] = $message;
                }
                foreach ($postSerialized['audits'] as $message) {
                    $message['_type'] = 'audit';
                    $messages[$message['id']] = $message;
                }
                ksort($messages);
                $tpl->setVariable('messages', $messages);

                $Result['content'] = $tpl->fetch('design:sensor_api_gui/posts/history.tpl');
                $Result['node_id'] = 0;

                $contentInfoArray = ['url_alias' => 'sensor/post/' . $postId . '/history'];
                $contentInfoArray['persistent_variable'] = false;
                if ($tpl->variable('persistent_variable') !== false) {
                    $contentInfoArray['persistent_variable'] = $tpl->variable('persistent_variable');
                }
                $Result['content_info'] = $contentInfoArray;
                $Result['path'] = [];

                return $Result;
            } else {
                $module->redirectTo('/sensor/posts/' . $postId);
                return;
            }
        }
    }

    try {

        if ($repository->getSearchService()->searchPosts(
                'id = ' . $postId . ' limit 1',
                [
                    'executionTimes' => false,
                    'readingStatuses' => false,
                    'capabilities' => false,
                ]
            )->totalCount === 0) {
            throw new NotFoundException();
        }

        if (isset($Params['Offset']) && $Params['Offset'] === 'pdf') {
            $pdf = SensorPdfExport::instance($repository, $postId);
            $pdf->generate();
            eZExecution::cleanExit();
        }

        if (!OpenPaSensorRepository::isOnlyUserContext()){

            $tpl->setVariable('post_id', (int)$postId);

            $tpl->setVariable('areas', json_encode($repository->getAreasTree()));
            $tpl->setVariable('categories', json_encode($repository->getCategoriesTree()));
            $tpl->setVariable('operators', json_encode($repository->getOperatorsTree()));
            $tpl->setVariable('groups', json_encode($repository->getGroupsTree()));
            $tpl->setVariable('settings', json_encode($repository->getSensorSettings()));

            $classRepository = new ClassRepository();
            $tpl->setVariable(
                'sensor_post',
                json_encode($classRepository->load($repository->getPostContentClassIdentifier()))
            );

            $Result = [];
            $Result['persistent_variable'] = $tpl->variable('persistent_variable');

            $layoutVersion = 1;
            if (eZINI::instance('ocsensor.ini')->hasVariable('SensorConfig', 'PostLayoutVersion')) {
                $layoutVersion = eZINI::instance('ocsensor.ini')->variable('SensorConfig', 'PostLayoutVersion');
            }
            $layoutPreference = eZPreferences::value('sensor_post_layout');
            if ($layoutPreference) {
                $layoutVersion = (int)$layoutPreference;
            }

            $Result['content'] = $tpl->fetch('design:sensor_api_gui/posts/v' . $layoutVersion . '/post.tpl');
        }else{
            $Result['content'] = $tpl->fetch('design:sensor_api_gui/posts.tpl');
        }
        $Result['node_id'] = 0;

        $contentInfoArray = array('url_alias' => 'sensor/post/' . $postId);
        $contentInfoArray['persistent_variable'] = false;
        if ($tpl->variable('persistent_variable') !== false) {
            $contentInfoArray['persistent_variable'] = $tpl->variable('persistent_variable');
        }
        $Result['content_info'] = $contentInfoArray;
        $Result['path'] = array();

        return $Result;

    } catch (NotFoundException $e) {
        eZDebug::writeError($e->getMessage(), __FILE__);
        return $module->handleError(eZError::KERNEL_ACCESS_DENIED, 'kernel', [
            'post_id' => $postId,
            'error' => $e->getMessage(),
        ]);

    } catch (BaseException $e) {
        eZDebug::writeError($e->getMessage(), __FILE__);
        return $module->handleError(eZError::KERNEL_NOT_FOUND, 'kernel', [
            'error' => $e->getMessage(),
        ]);
    }

}
