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
    $tpl->setVariable('groups', $repository->getGroupsTree());

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