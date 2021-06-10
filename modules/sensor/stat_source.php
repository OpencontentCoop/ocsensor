<?php

$Module = $Params['Module'];
$repositoryIdentifier = $Params['Repository'];

try {
    $repository = OCCustomSearchableRepositoryProvider::instance()->provideRepository($repositoryIdentifier);

    $tpl = eZTemplate::factory();
    $tpl->setVariable('persistent_variable', array());
    $tpl->setVariable('repository', $repository->getIdentifier());
    $columns = [];
    foreach ($repository->getFields() as $field) {
        $columns[] = [
            "data" => $field->getName(),
            "name" => $field->getName(),
            "title" => $field->getLabel(),
            "searchable" => true,
            "orderable" => !$field->isMultiValued()
        ];
    }
    $tpl->setVariable('columns', $columns);

    $Result = array();
    $Result['persistent_variable'] = $tpl->variable('persistent_variable');
    $Result['content'] = $tpl->fetch('design:sensor_api_gui/stat_source.tpl');
    $Result['node_id'] = 0;

    $contentInfoArray = array('url_alias' => 'sensor/stat_source/' . $repo);
    $contentInfoArray['persistent_variable'] = array();
    if ($tpl->variable('persistent_variable') !== false) {
        $contentInfoArray['persistent_variable'] = $tpl->variable('persistent_variable');
    }
    $Result['content_info'] = $contentInfoArray;
    $Result['path'] = array();
} catch (Exception $e) {
    return $Module->handleError(eZError::KERNEL_NOT_FOUND, 'kernel', ['error' => $e->getMessage()]);
}