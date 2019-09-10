<?php

/** @var eZModule $Module */
$Module = $Params['Module'];
$Http = eZHTTPTool::instance();
$Part = $Params['Part'] ? $Params['Part'] : 'users';
$tpl = eZTemplate::factory();
$currentUser = eZUser::currentUser();
$repository = OpenPaSensorRepository::instance();


if ($Http->hasPostVariable('SelectDefaultApprover')) {
    eZContentBrowse::browse(array('action_name' => 'SelectDefaultApprover',
        'return_type' => 'ObjectID',
        'class_array' => eZUser::fetchUserClassNames(),
        'start_node' => $repository->getOperatorsRootNode()->attribute('node_id'),
        'cancel_page' => '/sensor/config',
        'from_page' => '/sensor/config'), $Module);
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
                $params = array('attributes' => array('approver' => implode('-', $objectIdList)));
                $result = eZContentFunctions::updateAndPublishObject($firstAreaObject, $params);
                $Module->redirectTo('/sensor/config');

                return;
            }
        }
    }
}

$root = $repository->getRootNode();

if ($Part == 'areas') {
    $tpl->setVariable('areas_parent_node', $repository->getAreasRootNode());

} elseif ($Part == 'users') {
    $tpl->setVariable('user_parent_node', $repository->getUserRootNode());
    $tpl->setVariable('user_classes', eZUser::fetchUserClassNames());

} elseif ($Part == 'categories') {
    $tpl->setVariable('categories_parent_node', $repository->getCategoriesRootNode());

} elseif ($Part == 'operators') {
    $tpl->setVariable('operator_parent_node', $repository->getOperatorsRootNode());
    $tpl->setVariable('operator_class', eZContentClass::fetchByIdentifier('sensor_operator'));

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
    $tpl->setVariable('participant_roles', SensorPost::participantRoleNameMap());

    $allLanguages = eZContentLanguage::fetchList();
    $allLanguageLocales = eZContentLanguage::fetchLocaleList();
    $siteaccessList = array_column(eZSiteAccess::siteaccessList(), 'name');
    $languages = array();
    $ini = eZINI::instance();
    $locale = eZLocale::instance();
    $host = eZINI::instance()->variable('SiteSettings', 'SiteURL');

    $currentLanguage = array(
        $locale->localeCode() => array(
            'url' => '//' . $host,
            'name' => $locale->languageName(),
            'locale' => $locale->localeCode()
        )
    );

    if ($ini->hasVariable('RegionalSettings', 'TranslationSA') && count($ini->variable('RegionalSettings', 'TranslationSA')) > 0) {
        $translationSiteAccesses = $ini->variable('RegionalSettings', 'TranslationSA');
        foreach ($translationSiteAccesses as $siteAccessName => $translationName) {
            if (in_array($siteAccessName, $siteaccessList)) {
                $host = eZSiteAccess::getIni($siteAccessName)->variable('SiteSettings', 'SiteURL');
                $locale = eZSiteAccess::getIni($siteAccessName)->variable('RegionalSettings', 'ContentObjectLocale');
                if (in_array($locale, $allLanguageLocales)) {
                    $languages[$locale] = array(
                        'url' => '//' . $host,
                        'name' => $translationName,
                        'locale' => $locale
                    );
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
    $samplePost = $repository->getPostRootNode()->subtree(array(
        'Limit' => 1,
        'ClassFilterType' => 'include',
        'ClassFilterInclude' => array('sensor_post'),
        'SortBy' => array('published', false)
    ));
    $tpl->setVariable('sample_post_id', $samplePost[0] instanceof eZContentObjectTreeNode ? $samplePost[0]->attribute('contentobject_id') : 0);
}

$data = array();
/** @var eZContentObjectTreeNode[] $otherFolders */
$otherFolders = (array)$repository->getRootNode()->subTree(array(
    'ClassFilterType' => 'include',
    'ClassFilterArray' => array('folder'),
    'Depth' => 1,
    'DepthOperator' => 'eq')
);
foreach ($otherFolders as $folder) {
    if ($folder->attribute('contentobject_id') != $repository->getCategoriesRootNode()->attribute('contentobject_id')) {
        $data[] = $folder;
    }
}

$tpl->setVariable('current_part', $Part);
$tpl->setVariable('data', $data);
$tpl->setVariable('root', $root);
$tpl->setVariable('post_container_node', $repository->getPostRootNode());
$tpl->setVariable('moderation_is_enabled',  $repository->isModerationEnabled());
$tpl->setVariable('current_user', $currentUser);
$tpl->setVariable('persistent_variable', array());

$Result = array();
$Result['persistent_variable'] = $tpl->variable('persistent_variable');
$Result['content'] = $tpl->fetch('design:sensor_api_gui/config.tpl');
$Result['node_id'] = 0;

$contentInfoArray = array('url_alias' => 'sensor/config');
$contentInfoArray['persistent_variable'] = false;
if ($tpl->variable('persistent_variable') !== false) {
    $contentInfoArray['persistent_variable'] = $tpl->variable('persistent_variable');
}
$Result['content_info'] = $contentInfoArray;
$Result['path'] = array();
