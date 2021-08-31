<?php
/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();

if (OpenPaSensorRepository::instance()->getSensorSettings()->get('ShowSmartGui')){
    $module->redirectTo('/#add');
    return;
}

$node = OpenPaSensorRepository::instance()->getPostRootNode();
$class = OpenPaSensorRepository::instance()->getPostContentClass();

if ($node instanceof eZContentObjectTreeNode && $class instanceof eZContentClass) {
    $languageCode = eZINI::instance()->variable('RegionalSettings', 'Locale');
    $object = eZContentObject::createWithNodeAssignment($node,
        $class->attribute('id'),
        $languageCode,
        false);
    if ($object) {
        eZSys::addAccessPath(array('layout', 'set', 'sensor_add'), 'layout', false);
        $module->redirectTo('content/edit/' . $object->attribute('id') . '/' . $object->attribute('current_version'));
        return;
    } else {
        return $module->handleError(eZError::KERNEL_ACCESS_DENIED, 'kernel');
    }
} else {
    return $module->handleError(eZError::KERNEL_NOT_AVAILABLE, 'kernel');
}