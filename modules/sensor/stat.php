<?php

$Module = $Params['Module'];
$chartIdentifier = $Params['ChartIdentifier'];
$repository = OpenPaSensorRepository::instance();
try {
    $charts = $repository->getStatisticsService()->getStatisticFactories();
    $current = $chartIdentifier ? $repository->getStatisticsService()->getStatisticFactoryByIdentifier($chartIdentifier) : $charts[0];

    $tpl = eZTemplate::factory();
    $tpl->setVariable('persistent_variable', array());
    $tpl->setVariable('list', $charts);
    $tpl->setVariable('current', $current);
    $tpl->setVariable('areas', $repository->getAreasTree());
    $tpl->setVariable('categories', $repository->getCategoriesTree());

    $groupTree = $repository->getGroupsTree();

    $groupsTag = [];
    $hasGroupsTag = false;

    $groupsReference = [];
    $hasGroupsReference = false;

    foreach ($groupTree->attribute('children') as $groupTreeItem) {
        $groupTag = $groupTreeItem->attribute('group');
        if (empty($groupTag)) {
            $groupTag = $groupTreeItem->attribute('name');
        }else{
            $hasGroupsTag = true;
        }
        if (!isset($groupsTag[$groupTag])){
            $groupsTag[$groupTag] = [
                'has_tag' => !empty($groupTreeItem->attribute('group')),
                'items' => [],
                'count' => 0,
            ];
        }
        $groupsTag[$groupTag]['items'][] = $groupTreeItem;
        $groupsTag[$groupTag]['count'] = $groupsTag[$groupTag]['count'] + 1;

        $groupReference = $groupTreeItem->attribute('reference');
        if (!empty($groupReference)) {
            $hasGroupsReference = true;
            $groupReferenceIdentifier = eZCharTransform::instance()->transformByGroup($groupReference, 'urlalias');
            $groupsReference[$groupReferenceIdentifier] = $groupReference;
        }
    }
    $tpl->setVariable('has_group_tag', $hasGroupsTag);
    $tpl->setVariable('groups', $groupsTag);

    $hasGroupsReference = $hasGroupsReference && $current->attribute('render_settings')['allow_reference_filter'];
    $tpl->setVariable('has_group_reference', $hasGroupsReference);
    $tpl->setVariable('references', $groupsReference);

    $Result = array();
    $Result['persistent_variable'] = $tpl->variable('persistent_variable');
    $Result['content'] = $tpl->fetch('design:sensor_api_gui/stat.tpl');
    $Result['node_id'] = 0;

    $contentInfoArray = array('url_alias' => 'sensor/stat');
    $contentInfoArray['persistent_variable'] = array();
    if ($tpl->variable('persistent_variable') !== false) {
        $contentInfoArray['persistent_variable'] = $tpl->variable('persistent_variable');
    }
    $Result['content_info'] = $contentInfoArray;
    $Result['path'] = array();

}catch (Exception $e){
    return $Module->handleError(eZError::KERNEL_NOT_FOUND, 'kernel', ['error' => $e->getMessage()]);
}
