<?php

use Opencontent\Opendata\Api\ClassRepository;
use Opencontent\Opendata\Api\Gateway\FileSystem;
use Opencontent\Opendata\Api\Values\Content;
use Opencontent\Sensor\Api\Exception\BaseException;
use Opencontent\Sensor\Api\Values\Settings;
use Opencontent\Sensor\Core\ActionDefinitions;
use Opencontent\Sensor\Core\PermissionDefinitions;
use Opencontent\Sensor\Legacy\Listeners\ApproverFirstReadListener;
use Opencontent\Sensor\Legacy\Listeners\MailNotificationListener;
use Opencontent\Sensor\Legacy\Listeners\PrivateMailNotificationListener;
use Opencontent\Sensor\Legacy\Listeners\ReminderNotificationListener;
use Opencontent\Sensor\Legacy\Listeners\ScenarioListener;
use Opencontent\Sensor\Legacy\Listeners\SendMailListener;
use Opencontent\Sensor\Legacy\Listeners\WelcomeOperatorListener;
use Opencontent\Sensor\Legacy\NotificationTypes;
use Opencontent\Sensor\Legacy\Repository as LegacyRepository;
use Opencontent\Sensor\Legacy\Scenarios;
use Opencontent\Sensor\Legacy\Statistics;
use Opencontent\Sensor\Legacy\Utils\TreeNode;

class OpenPaSensorRepository extends LegacyRepository
{
    protected $data = array();

    protected static $instance;

    private $settings;

    public static function instance()
    {
        //@todo load from ini
        if (self::$instance === null)
            self::$instance = new static();
        return self::$instance;
    }

    protected function __construct()
    {
        $firstApproverScenario = new Scenarios\FirstAreaApproverScenario($this);
        $restrictResponders = $this->getSensorSettings()->get('ForceUrpApproverOnFix') ? $firstApproverScenario->getApprovers() : [];

        $permissionDefinitions = array();
        $permissionDefinitions[] = new PermissionDefinitions\CanAddArea();
        $permissionDefinitions[] = new PermissionDefinitions\CanAddCategory();
        $permissionDefinitions[] = new PermissionDefinitions\CanAddObserver();
        $permissionDefinitions[] = new PermissionDefinitions\CanAssign();
        $permissionDefinitions[] = new PermissionDefinitions\CanChangePrivacy();
        $permissionDefinitions[] = new PermissionDefinitions\CanClose($restrictResponders);
        $permissionDefinitions[] = new PermissionDefinitions\CanComment();
        $permissionDefinitions[] = new PermissionDefinitions\CanFix();
        $permissionDefinitions[] = new PermissionDefinitions\CanForceFix();
        $permissionDefinitions[] = new PermissionDefinitions\CanModerate();
        $permissionDefinitions[] = new PermissionDefinitions\CanRespond($restrictResponders);
        $permissionDefinitions[] = new PermissionDefinitions\CanSendPrivateMessage();
        $permissionDefinitions[] = new PermissionDefinitions\CanSetExpiryDays();
        if ($this->getSensorSettings()->get('ApproverCanReopen') || $this->getSensorSettings()->get('AuthorCanReopen')) {
            $permissionDefinitions[] = new PermissionDefinitions\CanReopen(
                $this->getSensorSettings()->get('ApproverCanReopen'),
                $this->getSensorSettings()->get('AuthorCanReopen')
            );
        }
        //$permissionDefinitions[] = new PermissionDefinitions\CanRead();
        $permissionDefinitions[] = new \Opencontent\Sensor\Legacy\PermissionDefinitions\CanRead();
        $permissionDefinitions[] = new \Opencontent\Sensor\Legacy\PermissionDefinitions\CanEdit();
        $permissionDefinitions[] = new \Opencontent\Sensor\Legacy\PermissionDefinitions\CanRemove();
        $permissionDefinitions[] = new PermissionDefinitions\CanAddAttachment();
        $permissionDefinitions[] = new PermissionDefinitions\CanRemoveAttachment();
        $permissionDefinitions[] = new PermissionDefinitions\CanAddApprover();
        $permissionDefinitions[] = new PermissionDefinitions\CanAutoAssign();
        $permissionDefinitions[] = new PermissionDefinitions\CanRemoveObserver();
        $permissionDefinitions[] = new PermissionDefinitions\CanSelectReceiverInPrivateMessage($this->getSensorSettings()->get('UseDirectPrivateMessage'));
        $permissionDefinitions[] = new \Opencontent\Sensor\Legacy\PermissionDefinitions\CanAddImage();
        $permissionDefinitions[] = new \Opencontent\Sensor\Legacy\PermissionDefinitions\CanRemoveImage();
        $permissionDefinitions[] = new PermissionDefinitions\CanModerateComment();
        $this->setPermissionDefinitions($permissionDefinitions);

        $actionDefinitions = array();
        $actionDefinitions[] = new ActionDefinitions\AddAreaAction();
        $actionDefinitions[] = new ActionDefinitions\AddCategoryAction();
        $actionDefinitions[] = new ActionDefinitions\AddCommentAction();
        $actionDefinitions[] = new ActionDefinitions\AddResponseAction();
        $actionDefinitions[] = new ActionDefinitions\AddObserverAction();
        $actionDefinitions[] = new ActionDefinitions\AssignAction();
        $actionDefinitions[] = new ActionDefinitions\AutoAssignAction();
        $actionDefinitions[] = new ActionDefinitions\CloseAction();
        $actionDefinitions[] = new ActionDefinitions\EditCommentAction();
        $actionDefinitions[] = new ActionDefinitions\EditPrivateMessageAction();
        $actionDefinitions[] = new ActionDefinitions\EditResponseAction();
        $actionDefinitions[] = new ActionDefinitions\FixAction();
        $actionDefinitions[] = new ActionDefinitions\ForceFixAction();
        $actionDefinitions[] = new ActionDefinitions\MakePrivateAction();
        $actionDefinitions[] = new ActionDefinitions\MakePublicAction();
        $actionDefinitions[] = new ActionDefinitions\ModerateAction();
        $actionDefinitions[] = new ActionDefinitions\ReadAction();
        if ($this->getSensorSettings()->get('ApproverCanReopen') || $this->getSensorSettings()->get('AuthorCanReopen')) {
            $actionDefinitions[] = new ActionDefinitions\ReopenAction();
        }
        $actionDefinitions[] = new ActionDefinitions\SendPrivateMessageAction();
        $actionDefinitions[] = new ActionDefinitions\SetExpiryAction();
        $actionDefinitions[] = new ActionDefinitions\AddAttachmentAction();
        $actionDefinitions[] = new ActionDefinitions\RemoveAttachmentAction();
        $actionDefinitions[] = new ActionDefinitions\AddApproverAction();
        $actionDefinitions[] = new ActionDefinitions\RemoveObserverAction();
        $actionDefinitions[] = new ActionDefinitions\AddImageAction();
        $actionDefinitions[] = new ActionDefinitions\RemoveImageAction();
        $actionDefinitions[] = new ActionDefinitions\ModerateCommentAction();
        $this->setActionDefinitions($actionDefinitions);

        $this->addListener('on_approver_first_read', new ApproverFirstReadListener($this));

        $notificationTypes = [];
        $notificationTypes[] = new NotificationTypes\OnCreateNotificationType();
        $this->addListener('on_create', new MailNotificationListener($this));

        $notificationTypes[] = new NotificationTypes\OnAssignNotificationType();
        $this->addListener('on_assign', new MailNotificationListener($this));

        $notificationTypes[] = new NotificationTypes\OnGroupAssignNotificationType();
        $this->addListener('on_group_assign', new MailNotificationListener($this));

        $notificationTypes[] = new NotificationTypes\OnAddObserverNotificationType();
        $this->addListener('on_add_observer', new MailNotificationListener($this));

        $notificationTypes[] = new NotificationTypes\OnAddCommentNotificationType();
        $this->addListener('on_add_comment', new MailNotificationListener($this));

        $notificationTypes[] = new NotificationTypes\OnFixNotificationType();
        $this->addListener('on_fix', new MailNotificationListener($this));

        $notificationTypes[] = new NotificationTypes\OnCloseNotificationType();
        $this->addListener('on_close', new MailNotificationListener($this));

        $notificationTypes[] = new NotificationTypes\OnReopenNotificationType();
        $this->addListener('on_reopen', new MailNotificationListener($this));

        $notificationTypes[] = new NotificationTypes\ReminderNotificationType();
        $this->addListener('reminder', new ReminderNotificationListener($this));

        $notificationTypes[] = new NotificationTypes\OnSendPrivateMessageNotificationType();
        $this->addListener('on_send_private_message', new PrivateMailNotificationListener($this));

        $notificationTypes[] = new NotificationTypes\OnAddApproverNotificationType();
        $this->addListener('on_add_approver', new MailNotificationListener($this));

        $notificationTypes[] = new NotificationTypes\OnAddCommentToModerateNotificationType();
        $this->addListener('on_add_comment_to_moderate', new MailNotificationListener($this));

        $this->addListener('on_create', new SendMailListener($this));
        $this->addListener('after_run_action', new SendMailListener($this));

        $this->getNotificationService()->setNotificationTypes($notificationTypes);

        $this->addListener('*', new ScenarioListener($this));
        $this->addListener('on_new_operator', new WelcomeOperatorListener($this));

        $statisticsFactories = [];
        $statisticsFactories[] = new Statistics\StatusPercentage($this);
        $statisticsFactories[] = new Statistics\PerCategory($this);
        $statisticsFactories[] = new Statistics\PerArea($this);
        $statisticsFactories[] = new Statistics\PerType($this);
        $statisticsFactories[] = new Statistics\AvgTimes($this);
        $statisticsFactories[] = new Statistics\Users($this);
        $statisticsFactories[] = new Statistics\StatusPerCategory($this);
        $statisticsFactories[] = new Statistics\StatusPerOwnerGroup($this);
        $statisticsFactories[] = new Statistics\PostAging($this);
        $this->getStatisticsService()->setStatisticFactories($statisticsFactories);

        if (in_array('ocwebhookserver', eZExtension::activeExtensions())) {
            $this->addListener('*', new SensorWebHookListener($this));
        }

        $this->addListener('*', new SensorFlashMessageListener($this));
        if ($this->getSensorSettings()->get('SocketIsEnabled')) {
            $this->addListener('*', new SensorSocketEmitterListener(
                $this,
                $this->getSensorSettings()->get('SocketSecret'),
                $this->getSensorSettings()->get('SocketInternalUrl'),
                $this->getSensorSettings()->get('SocketPort')
            ));
        }

        eZModule::setGlobalPathList(eZModule::activeModuleRepositories());

        parent::__construct();
    }

    private static function sensorRootRemoteId()
    {
        return OpenPABase::getCurrentSiteaccessIdentifier() . '_openpa_sensor';
    }

    public function getSensorSettings()
    {
        if ($this->settings === null) {
            $sensorIni = eZINI::instance('ocsensor.ini')->group('SensorConfig');
            $socketIni = eZINI::instance('ocsensor.ini')->group('SocketSettings');
            $this->settings = new Settings(array(
                'AllowMultipleApprover' => isset($sensorIni['AllowMultipleApprover']) ? $sensorIni['AllowMultipleApprover'] == 'enabled' : false,
                'AllowMultipleOwner' => isset($sensorIni['AllowMultipleOwner']) ? $sensorIni['AllowMultipleOwner'] == 'enabled' : false,
                'AuthorCanReopen' => isset($sensorIni['AuthorCanReopen']) ? $sensorIni['AuthorCanReopen'] == 'enabled' : false,
                'ApproverCanReopen' => isset($sensorIni['ApproverCanReopen']) ? $sensorIni['ApproverCanReopen'] == 'enabled' : false,
                'UniqueCategoryCount' => isset($sensorIni['CategoryCount']) ? $sensorIni['CategoryCount'] == 'unique' : true,
                'CategoryAutomaticAssign' => isset($sensorIni['CategoryAutomaticAssign']) ? $sensorIni['CategoryAutomaticAssign'] == 'enabled' : false,
                'DefaultPostExpirationDaysInterval' => isset($sensorIni['DefaultPostExpirationDaysInterval']) ? intval($sensorIni['DefaultPostExpirationDaysInterval']) : 15,
                'DefaultPostExpirationDaysLimit' => isset($sensorIni['DefaultPostExpirationDaysLimit']) ? intval($sensorIni['DefaultPostExpirationDaysLimit']) : 7,
                'TextMaxLength' => isset($sensorIni['TextMaxLength']) ? intval($sensorIni['TextMaxLength']) : 800,
                'CloseCommentsAfterSeconds' => isset($sensorIni['CloseCommentsAfterSeconds']) ? intval($sensorIni['CloseCommentsAfterSeconds']) : 1814400,
                'MoveMarkerOnSelectArea' => isset($sensorIni['MoveMarkerOnSelectArea']) ? $sensorIni['MoveMarkerOnSelectArea'] == 'enabled' : true,
                'CommentsAllowed' => isset($sensorIni['CommentsAllowed']) ? $sensorIni['CommentsAllowed'] == 'enabled' : true,
                'CategoryAutomaticAssignToRandomOperator' => isset($sensorIni['CategoryAutomaticAssignToRandomOperator']) ? $sensorIni['CategoryAutomaticAssignToRandomOperator'] == 'enabled' : false,
                'HidePrivacyChoice' => $this->isHiddenPrivacyChoice(),
                'HideTimelineDetails' => $this->isHiddenTimelineDetails(),
                'ForceUrpApproverOnFix' => isset($sensorIni['ForceUrpApproverOnFix']) ? $sensorIni['ForceUrpApproverOnFix'] == 'enabled' : false,
                'UseDirectPrivateMessage' => isset($sensorIni['UseDirectPrivateMessage']) ? $sensorIni['UseDirectPrivateMessage'] == 'enabled' : true,
                'HideTypeChoice' => $this->isHiddenTypeChoice(),
                'ShowSmartGui' => $this->isShownSmartGui(),
                'ShowResponseProposal' => isset($sensorIni['ShowResponseProposal']) ? $sensorIni['ShowResponseProposal'] == 'enabled' : false,
                'HideOperatorNames' => $this->isHiddenOperatorName(),
                'HiddenOperatorName' => 'Operatore',
                'HiddenOperatorEmail' => 'operator@example.it',
                'AnnounceKitId' => $this->getAnnounceKitId(),
                'MinimumIntervalFromLastPrivateMessageToFix' => isset($sensorIni['MinimumIntervalFromLastPrivateMessageToFix']) ? (int)$sensorIni['MinimumIntervalFromLastPrivateMessageToFix'] : -1,
                'SocketIsEnabled' => $socketIni['Enabled'] === 'true' || $socketIni['Enabled'] === true,
                'SocketUri' => $socketIni['Url'],
                'SocketPath' => $socketIni['Path'],
                'SocketPort' => $socketIni['Port'],
                'SocketInternalUrl' => $socketIni['InternalUrl'],
                'SocketSecret' => $socketIni['Secret'],
            ));
        }

        return $this->settings;
    }

    public function getCurrentUser()
    {
        if ($this->user === null)
            $this->user = $this->getUserService()->loadUser(eZUser::currentUserID());

        return $this->user;
    }

    public function setCurrentLanguage($language)
    {
        $this->language = $language;
//@todo
//        if ($this->language != eZLocale::currentLocaleCode()) {
//            $GLOBALS["eZLocaleStringDefault"] = $this->language;
//            //@todo svuotare cachce translations?
//        }
    }

    public function getCurrentLanguage()
    {
        if ($this->language === null)
            return eZLocale::currentLocaleCode();

        return $this->language;
    }

    public function getRootNode()
    {
        if (!isset($this->data['root']) || $this->data['root'] === null)
            $this->data['root'] = $this->fetchObjectRemoteID(self::sensorRootRemoteId())->attribute('main_node');
        return $this->data['root'];
    }

    public function getRootNodeAttribute($identifier)
    {
        if ($this->getRootNode()) {
            if (!isset($this->data['root_data_map'])) {
                $this->data['root_data_map'] = $this->getRootNode()->attribute('data_map');
            }
            if (isset($this->data['root_data_map'][$identifier])) {
                return $this->data['root_data_map'][$identifier];
            }
        }

        return null;
    }

    public function getOperatorsRootNode()
    {
        if (!isset($this->data['operators']))
            $this->data['operators'] = $this->fetchObjectRemoteID(self::sensorRootRemoteId() . '_operators')->attribute('main_node');
        return $this->data['operators'];
    }

    public function getCategoriesRootNode()
    {
        if (!isset($this->data['categories']))
            $this->data['categories'] = $this->fetchObjectRemoteID(self::sensorRootRemoteId() . '_postcategories')->attribute('main_node');
        return $this->data['categories'];
    }

    public function getAreasRootNode()
    {
        if (!isset($this->data['areas']))
            $this->data['areas'] = $this->getRootNode();
        return $this->data['areas'];
    }

    public function getGroupsRootNode()
    {
        if (!isset($this->data['groups'])) {
            $groups = $this->fetchObjectRemoteID(self::sensorRootRemoteId() . '_groups');
            if (!$groups instanceof eZContentObject) {
                eZDebug::writeError("Missing node whit remote id " . self::sensorRootRemoteId() . '_groups');
                $groups = $this->getRootNode();
            }
            $this->data['groups'] = $groups->attribute('main_node');
        }
        return $this->data['groups'];
    }

    public function getOperatorContentClass()
    {
        if (!isset($this->data['operator_class']))
            $this->data['operator_class'] = eZContentClass::fetchByIdentifier('sensor_operator');
        return $this->data['operator_class'];
    }

    public function getSensorCollaborationHandlerTypeString()
    {
        return 'openpasensor';
    }

    public function getPostRootNode()
    {
        if (!isset($this->data['posts']))
            $this->data['posts'] = $this->fetchObjectRemoteID(self::sensorRootRemoteId() . '_postcontainer')->attribute('main_node');
        return $this->data['posts'];
    }

    public function getPostContentClassIdentifier()
    {
        return 'sensor_post';
    }

    public function getPostApiClass()
    {
        if (!isset($this->data['post_api_class'])) {
            $classRepository = new ClassRepository();
            $this->data['post_api_class'] = $classRepository->load($this->getPostContentClassIdentifier());
        }
        return $this->data['post_api_class'];
    }

    public function getPostContentClass()
    {
        if (!isset($this->data['post_class']))
            $this->data['post_class'] = eZContentClass::fetchByIdentifier($this->getPostContentClassIdentifier());
        return $this->data['post_class'];
    }

    public function getPostContentClassAttribute($identifier)
    {
        if (!isset($this->data['post_class_data_map'])) {
            $this->data['post_class_data_map'] = $this->getPostContentClass()->dataMap();
        }
        return $this->data['post_class_data_map'][$identifier];
    }

    public function getUserRootNode()
    {
        if (!isset($this->data['users']))
            $this->data['users'] = eZContentObjectTreeNode::fetch(intval(eZINI::instance()->variable("UserSettings", "DefaultUserPlacement")));
        return $this->data['users'];
    }

    public function getSensorPostStates($identifier)
    {
        if (!isset($this->data['states_' . $identifier])) {
            if ($identifier == 'sensor') {
                $this->data['states_sensor'] = OpenPABase::initStateGroup(
                    'sensor',
                    array(
                        'pending' => "Inviato",
                        'open' => "In carico",
                        'close' => "Chiusa"
                    )
                );
            } elseif ($identifier == 'privacy') {
                $this->data['states_privacy'] = OpenPABase::initStateGroup(
                    'privacy',
                    array(
                        'public' => "Pubblico",
                        'private' => "Privato",
                    )
                );
            } elseif ($identifier == 'moderation') {
                $this->data['states_moderation'] = OpenPABase::initStateGroup(
                    'moderation',
                    array(
                        'skipped' => "Non necessita di moderazione",
                        'waiting' => "In attesa di moderazione",
                        'accepted' => "Accettato",
                        'refused' => "Rifiutato"
                    )
                );
            } else {
                throw new BaseException("Status $identifier not handled");
            }
        }
        return $this->data['states_' . $identifier];
    }

    public function isModerationEnabled()
    {
        $globalModeration = $this->getRootNodeAttribute('enable_moderation');
        return ($globalModeration && $globalModeration->attribute('data_type_string') == 'ezboolean' && $globalModeration->attribute('data_int') == 1);
    }

    public static function clearCache()
    {
        $repository = new static();
        TreeNode::clearCache($repository->getCategoriesRootNode()->attribute('node_id'));
        TreeNode::clearCache($repository->getAreasRootNode()->attribute('node_id'));
        TreeNode::clearCache($repository->getOperatorsRootNode()->attribute('node_id'));
        TreeNode::clearCache($repository->getGroupsRootNode()->attribute('node_id'));
        $commonPath = eZDir::path(array(eZSys::cacheDirectory(), 'ocopendata', 'sensor'));
        $fileHandler = eZClusterFileHandler::instance();
        $commonSuffix = '';
        $fileHandler->fileDeleteByDirList(array('content'), $commonPath, $commonSuffix);
    }

    public function addDefaultNotificationsToUser($userId)
    {
        try {
            $user = $this->getUserService()->loadUser($userId);
            foreach (['on_create', 'on_assign', 'on_close', 'reminder'] as $identifier) {
                $notification = $this->getNotificationService()->getNotificationByIdentifier($identifier);
                $this->getNotificationService()->addUserToNotification($user, $notification);
            }
        } catch (Exception $e) {
            eZDebug::writeError($e->getMessage(), __METHOD__);
        }
    }

    private function isHiddenPrivacyChoice()
    {
        $attribute = $this->getRootNodeAttribute('hide_privacy_choice');
        if ($attribute instanceof eZContentObjectAttribute) {
            return $attribute->attribute('data_int') == 1;
        }

        return false;
    }

    private function isHiddenTimelineDetails()
    {
        $attribute = $this->getRootNodeAttribute('hide_timeline_details');
        if ($attribute instanceof eZContentObjectAttribute) {
            return $attribute->attribute('data_int') == 1;
        }

        return true;
    }

    private function isHiddenTypeChoice()
    {
        $attribute = $this->getRootNodeAttribute('hide_type_choice');
        if ($attribute instanceof eZContentObjectAttribute) {
            return $attribute->attribute('data_int') == 1;
        }

        return false;
    }

    private function isShownSmartGui()
    {
        $attribute = $this->getRootNodeAttribute('show_smart_gui');
        if ($attribute instanceof eZContentObjectAttribute) {
            return $attribute->attribute('data_int') == 1;
        }

        return false;
    }

    private function isHiddenOperatorName()
    {
        $attribute = $this->getRootNodeAttribute('hide_operator_name');
        if ($attribute instanceof eZContentObjectAttribute) {
            return $attribute->attribute('data_int') == 1;
        }

        return false;
    }

    private function getAnnounceKitId()
    {
        $attribute = $this->getRootNodeAttribute('announce_kit_id');
        if ($attribute instanceof eZContentObjectAttribute && $attribute->hasContent()) {
            return $attribute->toString();
        }

        return false;
    }

    public function getConfigMenu()
    {
        $data = [
            'default' => [
                'uri' => 'sensor/config',
                'label' => ezpI18n::tr('sensor/config', 'Settings'),
                'node' => false,
                'icon' => 'fa fa-cogs',
            ],
            'users' => [
                'uri' => 'sensor/config/users',
                'label' => ezpI18n::tr('sensor/config', 'Utenti'),
                'node' => false,
                'icon' => 'fa fa-users',
            ],
            'operators' => [
                'uri' => 'sensor/config/operators',
                'label' => ezpI18n::tr('sensor/config', 'Operatori'),
                'node' => false,
                'icon' => 'fa fa-user-circle',
            ],
            'categories' => [
                'uri' => 'sensor/config/categories',
                'label' => ezpI18n::tr('sensor/config', 'Categorie'),
                'node' => false,
                'icon' => 'fa fa-tags',
            ],
            'areas' => [
                'uri' => 'sensor/config/areas',
                'label' => ezpI18n::tr('sensor/config', 'Zone'),
                'node' => false,
                'icon' => 'fa fa-map-marker',
            ],
            'groups' => [
                'uri' => 'sensor/config/groups',
                'label' => ezpI18n::tr('sensor/config', 'Gruppi'),
                'node' => false,
                'icon' => 'fa fa-user-circle-o'
            ],
//            'automations' => [
//                'uri' => 'sensor/config/automations',
//                'label' => ezpI18n::tr('sensor/config', 'Automazioni'),
//                'node' => false,
//                'icon' => 'fa fa-android',
//            ],
        ];
        /** @var eZContentObjectTreeNode[] $otherFolders */
        $otherFolders = (array)$this->getRootNode()->subTree(array(
                'ClassFilterType' => 'include',
                'ClassFilterArray' => array('folder'),
                'Depth' => 1,
                'DepthOperator' => 'eq')
        );
        foreach ($otherFolders as $folder) {
            if (
                $folder->attribute('contentobject_id') != $this->getCategoriesRootNode()->attribute('contentobject_id')
                && $folder->attribute('contentobject_id') != $this->getGroupsRootNode()->attribute('contentobject_id')
                && $folder->attribute('contentobject_id') != $this->getScenariosRootNode()->attribute('contentobject_id')
            ) {
                $data['data-' . $folder->attribute('contentobject_id')] = [
                    'uri' => 'sensor/config/' . 'data-' . $folder->attribute('contentobject_id'),
                    'label' => $folder->attribute('name'),
                    'node' => $folder,
                    'icon' => 'fa fa-folder'
                ];
            }
        }
        if (eZUser::currentUser()->hasAccessTo('*', '*')['accessWord'] == 'yes') {
            $data['automations'] = [
                'uri' => 'sensor/config/automations',
                'label' => ezpI18n::tr('sensor/config', 'Automazioni'),
                'node' => false,
                'icon' => 'fa fa-android',
            ];
        }

        $data['notifications'] = [
            'uri' => 'sensor/config/notifications',
            'label' => ezpI18n::tr('sensor/config', 'Testi notifiche'),
            'node' => false,
            'icon' => 'fa fa-align-left',
        ];

        return $data;
    }

    public function getScenariosRootNode()
    {
        if (!isset($this->data['scenarios'])) {
            $this->data['scenarios'] = $this->fetchObjectRemoteID(self::sensorRootRemoteId() . '_scenarios')->attribute('main_node');
        }
        return $this->data['scenarios'];
    }

    private function fetchObjectRemoteID($id)
    {
        $storage = new FileSystem();
        try {
            $content = $storage->loadContent($id);
            if ($content instanceof Content) {
                return $content->getContentObject($this->getCurrentLanguage());
            }
        } catch (Exception $e) {

        }
        return eZContentObject::fetchByRemoteID($id);
    }
}
