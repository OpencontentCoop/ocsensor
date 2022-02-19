<?php

/** @var eZModule $Module */

use Opencontent\Sensor\Legacy\Scenarios\SensorScenario;

$Module = $Params['Module'];
$Http = eZHTTPTool::instance();
$Part = $Params['Part'] ? $Params['Part'] : 'default';
$tpl = eZTemplate::factory();
$currentUser = eZUser::currentUser();
$repository = OpenPaSensorRepository::instance();
$root = $repository->getRootNode();
$rootObject = $root->object();

if ($Part == '_set') {
    if (!$rootObject->canEdit()) {
        $data = [
            'result' => 'fail',
            'message' => 'Unauthorized',
        ];
    } else {
        $key = $Http->variable('key');
        $value = intval($Http->variable('value') === 'true');
        if (strpos($key, 'stat-access-') !== false) {
            $permission = str_replace('stat-access-', '', $key);
            try {
                [$scope, $statIdentifier] = explode('-', $permission, 2);
                $data = SensorStatisticAccess::instance()->setAccess($scope, $statIdentifier, $value);
                $data = [
                    'result' => 'success',
                    'attributes' => [$permission => $value],
                ];
            } catch (Exception $e) {
                $data['message'] = $e->getMessage();
            }
        } else {
            $attribute = false;
            switch ($key) {
                case 'Moderation':
                    $attribute = 'enable_moderation';
                    break;
                case 'HidePrivacyChoice':
                    $attribute = 'hide_privacy_choice';
                    break;
                case 'HideTimelineDetails':
                    $attribute = 'hide_timeline_details';
                    break;
                case 'HideTypeChoice':
                    $attribute = 'hide_type_choice';
                    break;
                case 'ShowSmartGui':
                    $attribute = 'show_smart_gui';
                    break;
                case 'HideOperatorNames':
                    $attribute = 'hide_operator_name';
                    break;
            }
            if ($attribute) {
                $version = $rootObject->currentVersion();
                $availableLanguages = $version->translationList(false, false);
                foreach ($availableLanguages as $languageCode) {
                    $result = eZContentFunctions::updateAndPublishObject($rootObject, [
                        'attributes' => [$attribute => $value],
                        'language' => $languageCode,
                    ]);
                }
                if ($result) {
                    $data = [
                        'result' => 'success',
                        'attributes' => [$attribute => $value],
                    ];
                } else {
                    $data['message'] = "Fail updating";
                }
            } else {
                $data['message'] = "Attribute not found for key";
            }
        }
    }
    header('Content-Type: application/json');
    echo json_encode($data);
    eZExecution::cleanExit();
}

if ($Http->hasPostVariable('SelectDefaultApprover')) {
    eZContentBrowse::browse(['action_name' => 'SelectDefaultApprover',
        'return_type' => 'ObjectID',
        'class_array' => array_merge(eZUser::fetchUserClassNames(), ['sensor_group']),
        'start_node' => $repository->getOperatorsRootNode()->attribute('node_id'),
        'cancel_page' => '/sensor/config',
        'from_page' => '/sensor/config'], $Module);
    return;
}

if ($Http->hasPostVariable('BrowseActionName')
    && $Http->postVariable('BrowseActionName') == 'SelectDefaultApprover') {
    $objectIdList = $Http->postVariable('SelectedObjectIDArray');
    if (!empty($objectIdList)) {
        /** @var \Opencontent\Sensor\Legacy\Utils\TreeNodeItem[] $areas */
        $areas = $repository->getAreasTree()->attribute('children');
        $firstAreaId = $areas[0]->attribute('id');
        $firstAreaObject = eZContentObject::fetch((int)$firstAreaId);
        if ($firstAreaObject instanceof eZContentObject) {
            /** @var eZContentObjectAttribute[] $areaDataMap */
            $areaDataMap = $firstAreaObject->attribute('data_map');
            if (isset($areaDataMap['approver'])) {
                $params = ['attributes' => ['approver' => implode('-', $objectIdList)]];
                $version = $rootObject->currentVersion();
                $availableLanguages = $version->translationList(false, false);
                foreach ($availableLanguages as $languageCode) {
                    $params['language'] = $languageCode;
                    $result = eZContentFunctions::updateAndPublishObject($firstAreaObject, $params);
                }
                $Module->redirectTo('/sensor/config');

                return;
            }
        }
    }
}


if ($Part == 'areas') {
    $tpl->setVariable('areas_parent_node', $repository->getAreasRootNode());
    $tpl->setVariable('operators', json_encode($repository->getOperatorsTree()));
    $tpl->setVariable('groups', json_encode($repository->getGroupsTree()));

} elseif ($Part == 'users') {
    $tpl->setVariable('user_parent_node', $repository->getUserRootNode());
    $tpl->setVariable('user_classes', eZUser::fetchUserClassNames());
    $tpl->setVariable('user_groups', $repository->getMembersAvailableGroups());

} elseif ($Part == 'user_groups') {
    $tpl->setVariable('user_groups_parent_node_id', (int)\eZINI::instance()->variable("UserSettings", "DefaultUserPlacement"));
    $tpl->setVariable('user_groups_class', 'user_group');
    $tpl->setVariable('operator_parent_object_id', $repository->getOperatorsRootNode()->attribute('contentobject_id'));

} elseif ($Part == 'categories') {
    $tpl->setVariable('categories_parent_node', $repository->getCategoriesRootNode());
    $tpl->setVariable('areas', $repository->getAreasTree());
    $tpl->setVariable('operators', json_encode($repository->getOperatorsTree()));
    $tpl->setVariable('groups', json_encode($repository->getGroupsTree()));

} elseif ($Part == 'operators') {
    $tpl->setVariable('operator_parent_node', $repository->getOperatorsRootNode());
    $tpl->setVariable('operator_class', eZContentClass::fetchByIdentifier('sensor_operator'));
    $tpl->setVariable('groups', $repository->getGroupsTree());

} elseif ($Part == 'groups') {
    $tpl->setVariable('groups_parent_node', $repository->getGroupsRootNode());
    $tpl->setVariable('group_class', 'sensor_group');

} elseif ($Part == 'automations') {
    $tpl->setVariable('areas', $repository->getAreasTree());
    $tpl->setVariable('categories', $repository->getCategoriesTree());
    $tpl->setVariable('operators', $repository->getOperatorsTree());
    $tpl->setVariable('groups', $repository->getGroupsTree());
    $tpl->setVariable('types', $repository->getPostTypeService()->loadPostTypes());
    $tpl->setVariable('events', SensorScenario::getAvailableEvents());
    $tpl->setVariable('scenario_parent_node', $repository->getScenariosRootNode());

} elseif ($Part == 'notifications') {
    if ($Http->hasPostVariable('StoreNotificationsText')) {
        $data = $Http->postVariable('NotificationsText');
        SensorNotificationTextHelper::storeTexts($data);
    }
    if ($Http->hasPostVariable('ResetNotificationsText')) {
        SensorNotificationTextHelper::reset();
    }
    $texts = SensorNotificationTextHelper::getTexts();
    $tpl->setVariable('notification_types', $repository->getNotificationService()->getNotificationTypes());
    $tpl->setVariable('participant_roles', $repository->getParticipantService()->loadParticipantRoleCollection()->getArrayCopy());

    $allLanguages = eZPersistentObject::fetchObjectList(eZContentLanguage::definition(), null, ['locale' => ['!=', 'ita-PA']]);
    $allLanguageLocales = eZContentLanguage::fetchLocaleList();
    $siteaccessList = array_column(eZSiteAccess::siteaccessList(), 'name');
    $languages = [];
    $ini = eZINI::instance();
    $locale = eZLocale::instance();
    $host = eZINI::instance()->variable('SiteSettings', 'SiteURL');

    $currentLanguage = [
        $locale->localeCode() => [
            'url' => '//' . $host,
            'name' => $locale->languageName(),
            'locale' => $locale->localeCode(),
        ],
    ];
    if ($ini->hasVariable('RegionalSettings', 'TranslationSA') && count($ini->variable('RegionalSettings', 'TranslationSA')) > 0) {
        $translationSiteAccesses = $ini->variable('RegionalSettings', 'TranslationSA');
        foreach ($translationSiteAccesses as $siteAccessName => $translationName) {
            if (in_array($siteAccessName, $siteaccessList)) {
                $host = eZSiteAccess::getIni($siteAccessName)->variable('SiteSettings', 'SiteURL');
                $locale = eZSiteAccess::getIni($siteAccessName)->variable('RegionalSettings', 'ContentObjectLocale');
                if (in_array($locale, $allLanguageLocales)) {
                    $languages[$locale] = [
                        'url' => '//' . $host,
                        'name' => $translationName,
                        'locale' => $locale,
                    ];
                }
            }
        }
    }
    if (empty($languages)) {
        $languages = $currentLanguage;
    }

    $tpl->setVariable('languages', $languages);
    $tpl->setVariable('all_languages', $allLanguages);
    $tpl->setVariable('texts', $texts);
    $samplePost = $repository->getPostRootNode()->subtree([
        'Limit' => 1,
        'ClassFilterType' => 'include',
        'ClassFilterArray' => ['sensor_post'],
        'SortBy' => ['published', false],
    ]);
    $tpl->setVariable('sample_post_id', $samplePost[0] instanceof eZContentObjectTreeNode ? $samplePost[0]->attribute('contentobject_id') : 0);

} elseif ($Part == 'statistics') {
    $tpl->setVariable('stats', $repository->getStatisticsService()->getStatisticFactories(true));
    $tpl->setVariable('scopes', SensorStatisticAccess::instance()->getScopes());
    $tpl->setVariable('current_accesses', SensorStatisticAccess::instance()->getCurrentAccessHash());

} elseif ($Part == 'faq') {
    $tpl->setVariable('faq_parent_node', $repository->getFaqRootNode());
    $tpl->setVariable('faq_class', 'sensor_faq');
    $tpl->setVariable('categories', $repository->getCategoriesTree());

} elseif ($Part == 'reports' && $repository->getReportsRootNode()) {
    $Http->setSessionVariable("LastAccessesURI", '/sensor/config/reports');
    if ($Http->hasGetVariable('make_static')) {
        $reportId = $Http->getVariable('make_static');
        $report = eZContentObject::fetch((int)$reportId);
        $store = false;
        if ($report instanceof eZContentObject && $report->attribute('class_identifier') == 'sensor_report') {
            $reportNode = $report->mainNode();
            if ($reportNode instanceof eZContentObjectTreeNode) {
                /** @var eZContentObjectTreeNode[] $items */
                $items = $reportNode->subTree([
                    'Depth' => 1,
                    'DepthOperator' => 'eq',
                    'SortBy' => ['attribute', true, 'sensor_report_item/priority'],
                    'Limitation' => [],
                ]);
                foreach ($items as $item) {
                    $data = SensorReport::generateItemData($item->object(), true);
                    if (!empty($data)) {
                        $store = true;
                    }
                }
            }
            if ($store) {
                $reportDataMap = $report->dataMap();
                if (isset($reportDataMap['static_at'])) {
                    $reportDataMap['static_at']->fromString(time());
                    $reportDataMap['static_at']->store();
                    eZSearch::addObject($report, true);
                }
            }
        }
        echo (int)$store;
        eZExecution::cleanExit();
    } elseif ($Http->hasGetVariable('change_visibility')) {
        $reportId = $Http->getVariable('change_visibility');
        $report = eZContentObject::fetch((int)$reportId);
        $store = false;
        if ($report instanceof eZContentObject && $report->attribute('class_identifier') == 'sensor_report') {
            $store = true;
            $privacyStates = $repository->getSensorPostStates('privacy');
            $public = $privacyStates['privacy.public'];
            $private = $privacyStates['privacy.private'];
            if (in_array($public->attribute('id'), $report->attribute('state_id_array'))) {
                $report->assignState($private);
            } else {
                $report->assignState($public);
            }
            eZSearch::addObject($report, true);
        }
        echo (int)$store;
        eZExecution::cleanExit();
    }
    $tpl->setVariable('report_parent_node', $repository->getReportsRootNode());
    $tpl->setVariable('report_class', 'sensor_report');

} elseif ($Part == 'translations') {

    $translationsHelper = SensorTranslationHelper::instance();

    if ($Http->hasPostVariable('AddCustom')) {
        $translationsHelper->addCustomTranslation(
            $Http->postVariable('Key'),
            $Http->postVariable('Languages')
        );
        $Module->redirectTo('/sensor/config/translations');
        return;
    }
    if ($Http->hasPostVariable('RemoveCustom')) {
        $keys = array_keys((array)$Http->postVariable('RemoveKeys'));
        $translationsHelper->removeCustomTranslations($keys);
        $Module->redirectTo('/sensor/config/translations');
        return;
    }


    $staticTranslations = [];
    $customTranslations = [];
    $availableLanguages = [];
    $languageCodeList = eZContentLanguage::fetchLocaleList();
    foreach ($languageCodeList as $languageCode){
        $static = $translationsHelper->loadStaticTranslations($languageCode);
        $custom = $translationsHelper->loadCustomTranslations($languageCode);
        if ($static){
            $availableLanguages[] = $languageCode;
            foreach ($static as $context => $values){
                foreach ($values as $key => $value){
                    $staticTranslations[$key][$languageCode] = $value;
                }
            }
            foreach ($custom as $context => $values){
                foreach ($values as $key => $value){
                    $customTranslations[$key][$languageCode] = $value;
                }
            }
        }
    }
    ksort($staticTranslations);
    $tpl->setVariable('static_translations', $staticTranslations);
    $tpl->setVariable('custom_translations', $customTranslations);
    $tpl->setVariable('available_languages', $availableLanguages);
    $tpl->setVariable('current_locale_code', eZLocale::currentLocaleCode());
}

$configMenu = $repository->getConfigMenu();
if (!isset($configMenu[$Part])) {
    return $Module->handleError(eZError::KERNEL_NOT_FOUND, 'kernel');
}
$tpl->setVariable('current_part', $Part);
$tpl->setVariable('menu', $configMenu);
$tpl->setVariable('root', $root);
$tpl->setVariable('post_container_node', $repository->getPostRootNode());
$tpl->setVariable('moderation_is_enabled', $repository->isModerationEnabled());
$tpl->setVariable('current_user', $currentUser);
$tpl->setVariable('persistent_variable', []);
$tpl->setVariable('sensor_settings', $repository->getSensorSettings()->jsonSerialize());

$Result = [];
$Result['persistent_variable'] = $tpl->variable('persistent_variable');
$Result['content'] = $tpl->fetch('design:sensor_api_gui/config.tpl');
$Result['node_id'] = 0;

$contentInfoArray = ['url_alias' => 'sensor/config'];
$contentInfoArray['persistent_variable'] = false;
if ($tpl->variable('persistent_variable') !== false) {
    $contentInfoArray['persistent_variable'] = $tpl->variable('persistent_variable');
}
$Result['content_info'] = $contentInfoArray;
$Result['path'] = [];
