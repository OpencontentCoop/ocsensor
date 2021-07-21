<?php

/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$remoteId = $Params['RemoteId'];
$action = $Params['Action'];
$slideId = $Params['SlideId'];
$http = eZHTTPTool::instance();

$repository = OpenPaSensorRepository::instance();
if (!$repository->getReportsRootNode()) {
    $module->redirectTo('/');
    return;
}

$report = eZContentObject::fetchByRemoteID($remoteId);
if ($report instanceof eZContentObject && $report->attribute('class_identifier') == 'sensor_report') {
    $tpl->setVariable('report', $report);
    $dataMap = $report->dataMap();
    $isEnabled = (isset($dataMap['enabled']) && $dataMap['enabled']->attribute('data_int') == 1);
    if (!$isEnabled){
        $module->redirectTo('/');
        return;
    }
    $password = false;
    if (isset($dataMap['password']) && $dataMap['password']->hasContent()) {
        $password = $dataMap['password']->content();
    }
    $passwordIsRequired = !empty($password);

    if (!empty($password) && $http->hasPostVariable('ReportAccess-'.$remoteId)) {
        if ($http->postVariable('ReportAccess-'.$remoteId) === $password) {
            $sessionValue = password_hash($password, PASSWORD_BCRYPT);
            $http->setSessionVariable('CanAccessReport_' . $report->attribute('id'), $sessionValue);
            $module->redirectTo('/sensor/report/' . $remoteId);
            return;
        }
    }

    if ($http->hasSessionVariable('CanAccessReport_' . $report->attribute('id'))) {
        $hash = $http->sessionVariable('CanAccessReport_' . $report->attribute('id'));
        $passwordIsRequired = !(password_verify($password, $hash));
    }

    if ($passwordIsRequired) {
        $Result = array();
        $Result['persistent_variable'] = $tpl->variable('persistent_variable');
        $Result['content'] = $tpl->fetch('design:report/access.tpl');
        $Result['node_id'] = 0;

        $contentInfoArray = array('url_alias' => 'sensor/report/' . $remoteId);
        $contentInfoArray['persistent_variable'] = false;
        if ($tpl->variable('persistent_variable') !== false) {
            $contentInfoArray['persistent_variable'] = $tpl->variable('persistent_variable');
        }
        $Result['content_info'] = $contentInfoArray;
        $Result['path'] = array();
        $Result['pagelayout'] = 'design:report/pagelayout.tpl';

        return $Result;
    } else {
        if ($action === 'logout') {
            $http->removeSessionVariable('CanAccessReport_' . $report->attribute('id'));
            $module->redirectTo('/sensor/report/' . $remoteId);
            return;
        }elseif ($action === 's'){
            $item = eZContentObject::fetch((int)$slideId);
            $data = [];
            if ($item instanceof eZContentObject && $item->mainParentNodeID() == $report->mainNodeID()){
                $data = SensorReport::getItemData($item);
            }
            header('Content-Type: application/json');
            echo json_encode( $data );
            eZExecution::cleanExit();
        }
        $reportNode = $report->mainNode();
        $tpl->setVariable('items', $reportNode->subTree([
            'Depth' => 1,
            'DepthOperator' => 'eq',
            'SortBy' => ['attribute', true, 'sensor_report_item/priority'],
            'Limitation' => []
        ]));
        $Result = array();
        $Result['persistent_variable'] = $tpl->variable('persistent_variable');
        $Result['content'] = $tpl->fetch('design:report/view.tpl');
        $Result['node_id'] = 0;

        $contentInfoArray = array('url_alias' => 'sensor/report/' . $remoteId);
        $contentInfoArray['persistent_variable'] = false;
        if ($tpl->variable('persistent_variable') !== false) {
            $contentInfoArray['persistent_variable'] = $tpl->variable('persistent_variable');
        }
        $Result['content_info'] = $contentInfoArray;
        $Result['path'] = array();
        $Result['pagelayout'] = 'design:report/pagelayout.tpl';

        return $Result;
    }
}

$module->redirectTo('/');