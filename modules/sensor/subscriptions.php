<?php
/** @var eZModule $Module */

$Module = $Params['Module'];
$Http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();
$repository = OpenPaSensorRepository::instance();

$subscriptions = $repository->getSubscriptionService()->getSubscriptionsByUser($repository->getCurrentUser());
$tpl->setVariable('subscriptions', json_decode(json_encode($subscriptions), true));

$Result = [];
$Result['persistent_variable'] = $tpl->variable('persistent_variable');
$Result['content'] = $tpl->fetch('design:sensor/subscriptions.tpl');
$Result['node_id'] = 0;

$contentInfoArray = ['url_alias' => 'sensor/subscriptions'];
$contentInfoArray['persistent_variable'] = false;
if ($tpl->variable('persistent_variable') !== false) {
    $contentInfoArray['persistent_variable'] = $tpl->variable('persistent_variable');
}
$Result['content_info'] = $contentInfoArray;
$Result['path'] = [];