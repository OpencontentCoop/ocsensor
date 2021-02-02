<?php

$module = $Params['Module'];
$id = (int)$Params['ID'];

$tpl = eZTemplate::factory();
$repository = OpenPaSensorRepository::instance();

$user = false;
if ($id > 0) {
    $user = $repository->getUserService()->loadUser($id);
    if ($user->type == '') {
        $module->redirectTo('sensor/user');
        return;
    }
}

$tpl->setVariable('user_parent_node', $repository->getUserRootNode());
$tpl->setVariable('user_classes', eZUser::fetchUserClassNames());


$Result = array();
$Result['persistent_variable'] = $tpl->variable('persistent_variable');
if ($id > 0) {
    $tpl->setVariable('user', $user);
    $Result['content'] = $tpl->fetch('design:sensor_api_gui/user.tpl');
} else {
    $Result['content'] = $tpl->fetch('design:sensor_api_gui/users.tpl');
}

$Result['node_id'] = 0;

$contentInfoArray = array('url_alias' => 'sensor/user');
$contentInfoArray['persistent_variable'] = array();
if ($tpl->variable('persistent_variable') !== false) {
    $contentInfoArray['persistent_variable'] = $tpl->variable('persistent_variable');
}
$Result['content_info'] = $contentInfoArray;
$Result['path'] = array();
