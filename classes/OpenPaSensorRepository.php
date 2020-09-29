<?php

use Opencontent\Sensor\Legacy\Listeners\SendMailListener;
use Opencontent\Sensor\Legacy\Repository as LegacyRepository;
use Opencontent\Sensor\Api\Exception\BaseException;
use Opencontent\Sensor\Core\PermissionDefinitions;
use Opencontent\Sensor\Core\ActionDefinitions;
use Opencontent\Sensor\Api\Values\Settings;
use Opencontent\Sensor\Legacy\PostService\Scenarios;
use Opencontent\Sensor\Legacy\PostService\ScenarioInterface;
use Opencontent\Sensor\Legacy\Listeners\MailNotificationListener;
use Opencontent\Sensor\Legacy\Listeners\ReminderNotificationListener;
use Opencontent\Sensor\Legacy\Listeners\PrivateMailNotificationListener;
use Opencontent\Sensor\Legacy\NotificationTypes;
use Opencontent\Sensor\Legacy\Statistics;
use Opencontent\Sensor\Legacy\Listeners\ApproverFirstReadListener;

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
        $restrictResponders = $this->getSensorSettings()->get('ForceUrpApproverOnFix') ?
            array_map('intval', $firstApproverScenario->getApprovers()) : [];

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
        $this->setActionDefinitions($actionDefinitions);

        $scenarios = [];
        $scenarios[ScenarioInterface::LOW] = $firstApproverScenario;
        $this->setScenarios($scenarios);

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

        $this->addListener('after_run_action', new SendMailListener($this));

        $this->getNotificationService()->setNotificationTypes($notificationTypes);

        $statisticsFactories = [];
        $statisticsFactories[] = new Statistics\StatusPercentage($this);
        $statisticsFactories[] = new Statistics\PerCategory($this);
        $statisticsFactories[] = new Statistics\PerArea($this);
        $statisticsFactories[] = new Statistics\PerType($this);
        $statisticsFactories[] = new Statistics\AvgTimes($this);
        $this->getStatisticsService()->setStatisticFactories($statisticsFactories);

        if (in_array('ocwebhookserver', eZExtension::activeExtensions())) {
            $this->addListener('*', new SensorWebHookListener($this));
        }

        $this->addListener('*', new SensorFlashMessageListener($this));

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
        if ($this->language != eZLocale::currentLocaleCode()) {
            //@todo
            //$GLOBALS["eZLocaleStringDefault"] = $this->language;
            //@todo svuotare cachce translations?
        }
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
            $this->data['root'] = eZContentObject::fetchByRemoteID(self::sensorRootRemoteId())->attribute('main_node');
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
            $this->data['operators'] = eZContentObject::fetchByRemoteID(self::sensorRootRemoteId() . '_operators')->attribute('main_node');
        return $this->data['operators'];
    }

    public function getCategoriesRootNode()
    {
        if (!isset($this->data['categories']))
            $this->data['categories'] = eZContentObject::fetchByRemoteID(self::sensorRootRemoteId() . '_postcategories')->attribute('main_node');
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
            $groups = eZContentObject::fetchByRemoteID(self::sensorRootRemoteId() . '_groups');
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
            $this->data['posts'] = eZContentObject::fetchByRemoteID(self::sensorRootRemoteId() . '_postcontainer')->attribute('main_node');
        return $this->data['posts'];
    }

    public function getPostContentClassIdentifier()
    {
        return 'sensor_post';
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
        return ($globalModeration && $globalModeration->attribute( 'data_type_string' ) == 'ezboolean' && $globalModeration->attribute( 'data_int' ) == 1);
    }

    public static function clearCache()
    {
        $repository = new static();
        \Opencontent\Sensor\Legacy\Utils\TreeNode::clearCache($repository->getCategoriesRootNode()->attribute('node_id'));
        \Opencontent\Sensor\Legacy\Utils\TreeNode::clearCache($repository->getAreasRootNode()->attribute('node_id'));
        \Opencontent\Sensor\Legacy\Utils\TreeNode::clearCache($repository->getOperatorsRootNode()->attribute('node_id'));
        \Opencontent\Sensor\Legacy\Utils\TreeNode::clearCache($repository->getGroupsRootNode()->attribute('node_id'));
        $commonPath = eZDir::path(array(eZSys::cacheDirectory(), 'sensor'));
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
        }catch (Exception $e){
            eZDebug::writeError($e->getMessage(), __METHOD__);
        }
    }

    private function isHiddenPrivacyChoice()
    {
        $attribute = $this->getRootNodeAttribute('hide_privacy_choice');
        if ($attribute instanceof eZContentObjectAttribute){
            return $attribute->attribute('data_int') == 1;
        }

        return false;
    }

    private function isHiddenTimelineDetails()
    {
        $attribute = $this->getRootNodeAttribute('hide_timeline_details');
        if ($attribute instanceof eZContentObjectAttribute){
            return $attribute->attribute('data_int') == 1;
        }

        return true;
    }
}
