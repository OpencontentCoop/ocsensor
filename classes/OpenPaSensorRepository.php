<?php

use League\Event\AbstractListener;
use Opencontent\Opendata\Api\ClassRepository;
use Opencontent\Sensor\Api\Exception\BaseException;
use Opencontent\Sensor\Api\Values\NotificationType;
use Opencontent\Sensor\Api\Values\Settings;
use Opencontent\Sensor\Core\ActionDefinitions;
use Opencontent\Sensor\Core\PermissionDefinitions;
use Opencontent\Sensor\Legacy\Listeners\SuperUserPostFixListener;
use Opencontent\Sensor\Legacy\PermissionDefinitions as LegacyPermissionDefinitions;
use Opencontent\Sensor\Legacy\Listeners\ApproverFirstReadListener;
use Opencontent\Sensor\Legacy\Listeners\ScenarioListener;
use Opencontent\Sensor\Legacy\Listeners\SendMailListener;
use Opencontent\Sensor\Legacy\Listeners\WelcomeOperatorListener;
use Opencontent\Sensor\Legacy\Listeners\WelcomeUserListener;
use Opencontent\Sensor\Legacy\Repository as LegacyRepository;
use Opencontent\Sensor\Legacy\Scenarios;
use Opencontent\Sensor\Legacy\Scenarios\FallbackScenario;
use Opencontent\Sensor\Legacy\ScenarioService;
use Opencontent\Sensor\Legacy\Statistics;
use Opencontent\Sensor\Legacy\Utils\TreeNode;
use Opencontent\Sensor\Api\Values\ParticipantRole;

class OpenPaSensorRepository extends LegacyRepository
{
    protected $data = array();

    protected static $instance;

    private $settings;

    private $isBuilt;

    public static function instance()
    {
        //@todo load from ini
        if (self::$instance === null) {
            self::$instance = new static();
            self::$instance->build();
        }
        return self::$instance;
    }

    protected function __construct()
    {
        eZModule::setGlobalPathList(eZModule::activeModuleRepositories());
    }

    protected function build()
    {
        if (!$this->isBuilt) {
            $this->language = eZLocale::currentLocaleCode();
            $firstApproverScenario = new Scenarios\FirstAreaApproverScenario($this);
            $restrictResponders = $this->getSensorSettings()->get('ForceUrpApproverOnFix') ? $firstApproverScenario->getUserApprovers() : [];

            $permissionDefinitions = [];
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
            $permissionDefinitions[] = new LegacyPermissionDefinitions\CanRead();
            $permissionDefinitions[] = new LegacyPermissionDefinitions\CanEdit();
            $permissionDefinitions[] = new LegacyPermissionDefinitions\CanRemove();
            $permissionDefinitions[] = new PermissionDefinitions\CanAddAttachment();
            $permissionDefinitions[] = new PermissionDefinitions\CanRemoveAttachment();
            $permissionDefinitions[] = new PermissionDefinitions\CanAddApprover();
            $permissionDefinitions[] = new PermissionDefinitions\CanAutoAssign($firstApproverScenario->getApprovers());
            $permissionDefinitions[] = new PermissionDefinitions\CanRemoveObserver();
            $permissionDefinitions[] = new PermissionDefinitions\CanSelectReceiverInPrivateMessage($this->getSensorSettings()->get('UseDirectPrivateMessage'));
            $permissionDefinitions[] = new LegacyPermissionDefinitions\CanAddImage();
            $permissionDefinitions[] = new LegacyPermissionDefinitions\CanRemoveImage();
            $permissionDefinitions[] = new PermissionDefinitions\CanModerateComment();
            $permissionDefinitions[] = new PermissionDefinitions\CanSetType();
            $permissionDefinitions[] = new LegacyPermissionDefinitions\CanAddFile();
            $permissionDefinitions[] = new LegacyPermissionDefinitions\CanRemoveFile();
            $permissionDefinitions[] = new PermissionDefinitions\CanSetTags();
            $permissionDefinitions[] = new PermissionDefinitions\CanSetProtocol();
            $permissionDefinitions[] = new PermissionDefinitions\CanSubscribe();
            $this->setPermissionDefinitions($permissionDefinitions);

            $actionDefinitions = [];
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
            $actionDefinitions[] = new ActionDefinitions\SetTypeAction();
            $actionDefinitions[] = new ActionDefinitions\AddFileAction();
            $actionDefinitions[] = new ActionDefinitions\RemoveFileAction();
            $actionDefinitions[] = new ActionDefinitions\SetTagsAction();
            $actionDefinitions[] = new ActionDefinitions\SetProtocolAction();
            $actionDefinitions[] = new ActionDefinitions\SubscribeAction();
            $actionDefinitions[] = new ActionDefinitions\RemoveSubscriptionAction();
            $this->setActionDefinitions($actionDefinitions);

            $this->addListener('on_approver_first_read', new ApproverFirstReadListener($this));

            $this->buildNotificationTypes();

            $this->addListener('on_create', new SendMailListener($this));
            $this->addListener('after_run_action', new SendMailListener($this));
            $this->addListener('*', new ScenarioListener($this));
            $this->addListener('on_new_operator', new WelcomeOperatorListener($this));
            $this->addListener('on_generate_user', new WelcomeUserListener($this));
            $this->addListener('on_create', new SensorDailyReportListener());
            $this->addListener('on_close', new SensorDailyReportListener());
            $this->addListener('on_update_operator', new SensorReindexer());
            $this->addListener('on_new_operator', new SensorReindexer());
            if ($this->getSensorSettings()->get('CloseOnUserGroupPostFix')) {
                $this->addListener('on_fix', new SuperUserPostFixListener($this));
            }
            $timelineListener = new SensorTimelineListener();
            $this->addListener('on_create', $timelineListener);
            $this->addListener('on_create_timeline', $timelineListener);

            $this->getStatisticsService()->setStatisticFactories([
                new Statistics\StatusPercentage($this),
                new Statistics\PerCategory($this),
                new Statistics\PerArea($this),
                new Statistics\PerType($this),
                new Statistics\AvgTimes($this),
                new Statistics\Users($this),
                new Statistics\StatusPerCategory($this),
                new Statistics\TypePerCategory($this),
                new Statistics\StatusPerOwnerGroup($this),
                new Statistics\OpenPerOwnerGroup($this),
                new Statistics\OpenHistoryPerOwnerGroup($this),
                new Statistics\PostAging($this),
                new Statistics\Trend($this),
                new Statistics\ExecutionTrend($this),
                new Statistics\ClosingTrend($this),
                new Statistics\ClosingTrendPerGroup($this),
            ]);

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

            $this->scenarioService = new ScenarioService($this, [$firstApproverScenario, new FallbackScenario()]);
            $this->isBuilt = true;
        }
    }

    private function buildNotificationTypes()
    {
        if (!$this->isBuilt){
            $types = eZINI::instance('ocsensor.ini')->variable('NotificationTypes', 'Types');
            foreach ($types as $type){
                $typeSettings = eZINI::instance('ocsensor.ini')->group('NotificationTypes_' . $type);
                $notificationClass = $typeSettings['PHPClass'];
                $listenerClass = $typeSettings['Listener'];
                if (!class_exists($notificationClass) || !class_exists($listenerClass)) {
                    $this->getLogger()->error("Notification $type classes not found: $notificationClass $listenerClass");
                    continue;
                }
                /** @var NotificationType $notificationType */
                $notificationType = new $notificationClass();
                if (!$notificationType instanceof NotificationType) {
                    $this->getLogger()->error("Notification $type php class $notificationClass must extends " . NotificationType::class);
                    continue;
                }
                /** @var AbstractListener $listener */
                $listener = new $listenerClass($this);
                if (!$listener instanceof AbstractListener){
                    $this->getLogger()->error("Notification $type listener class $listenerClass must extends " . AbstractListener::class);
                    continue;
                }

                $targetsMap = [
                    'TargetAuthor' => ParticipantRole::ROLE_AUTHOR,
                    'TargetApprover' => ParticipantRole::ROLE_APPROVER,
                    'TargetOwner' => ParticipantRole::ROLE_OWNER,
                    'TargetObserver' => ParticipantRole::ROLE_OBSERVER,
                ];
                foreach ($targetsMap as $settingKey => $role){
                    $targetSettings = isset($typeSettings[$settingKey]) ? $typeSettings[$settingKey] : '';
                    $targets = explode(';', $targetSettings);
                    $notificationType->setTarget($role, $targets);
                }
//                $this->getLogger()->debug("Add $notificationType->identifier",
//                    ['listener' => $listenerClass, 'targets' => json_encode($notificationType->getTargets())]
//                );
                $this->getNotificationService()->addNotificationType($notificationType);
                $this->addListener($notificationType->identifier, $listener);
            }
        }
    }

    private static function sensorRootRemoteId()
    {
        return OpenPABase::getCurrentSiteaccessIdentifier() . '_openpa_sensor';
    }

    public function clearSensorSettingsCache()
    {
        $cacheFile = 'settings.cache';
        $cacheFilePath = \eZDir::path(array(\eZSys::cacheDirectory(), 'ocopendata', 'sensor', $cacheFile));
        \eZClusterFileHandler::instance($cacheFilePath)->delete();
    }

    public function getSensorSettings()
    {
        if ($this->settings === null) {
            $modified = $this->getRootNode()->object()->attribute('modified');
            $cacheFile = 'settings.cache';
            $cacheFilePath = \eZDir::path(array(\eZSys::cacheDirectory(), 'ocopendata', 'sensor', $cacheFile));
            $settingsCacheManager = \eZClusterFileHandler::instance($cacheFilePath);
            $data = $settingsCacheManager->processCache(
                function ($file, $mtime, $extraData) {
                    if ($mtime >= $extraData[0]) {
                        $content = include($file);
                        return $content;
                    } else {
                        return new \eZClusterFileFailure(1, "Modified timestamp greater then file mtime");
                    }
                },
                function ($file, $args) {
                    $imagesAttribute = $this->getPostContentClassAttribute('images');
                    $filesAttribute = $this->getPostContentClassAttribute('files');
                    $sensorIni = eZINI::instance('ocsensor.ini')->group('SensorConfig');
                    $socketIni = eZINI::instance('ocsensor.ini')->group('SocketSettings');
                    $geocodeIni = eZINI::instance('ocsensor.ini')->group('GeoCoderSettings');
                    $data = array(
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
                        'HiddenOperatorName' => SensorTranslationHelper::instance()->translate('Operator'),
                        'HiddenOperatorEmail' => 'operator@example.it',
                        'HiddenApproverName' => SensorTranslationHelper::instance()->translate('Reference for the citizen'),
                        'AnnounceKitId' => $this->getAnnounceKitId(),
                        'MinimumIntervalFromLastPrivateMessageToFix' => isset($sensorIni['MinimumIntervalFromLastPrivateMessageToFix']) ? (int)$sensorIni['MinimumIntervalFromLastPrivateMessageToFix'] : -1,
                        'SocketIsEnabled' => $socketIni['Enabled'] === 'true' || $socketIni['Enabled'] === true,
                        'SocketUri' => $socketIni['Url'],
                        'SocketPath' => $socketIni['Path'],
                        'SocketPort' => $socketIni['Port'],
                        'SocketInternalUrl' => $socketIni['InternalUrl'],
                        'SocketSecret' => $socketIni['Secret'],
                        'AllowChangeApprover' => isset($sensorIni['AllowChangeApprover']) ? $sensorIni['AllowChangeApprover'] == 'enabled' : false,
                        'ShowFaqCategories' => isset($sensorIni['ShowFaqCategories']) ? $sensorIni['ShowFaqCategories'] == 'enabled' : true,
                        'UseStatCalculatedColor' => isset($sensorIni['UseStatCalculatedColor']) ? $sensorIni['UseStatCalculatedColor'] == 'enabled' : true,
                        'MarkerMustBeInArea' => isset($geocodeIni['MarkerMustBeInArea']) ? $geocodeIni['MarkerMustBeInArea'] == 'enabled' : false,
                        'MarkerOutOfBoundsAlert' => $geocodeIni['MarkerOutOfBoundsAlert'],
                        'UseInboxContextActions' => isset($sensorIni['UseInboxContextActions']) ? $sensorIni['UseInboxContextActions'] == 'enabled' : true,
                        'UseInboxFilters' => isset($sensorIni['UseInboxFilters']) ? $sensorIni['UseInboxFilters'] == 'enabled' : true,
                        'AllowAdditionalMemberGroups' => isset($sensorIni['AllowAdditionalMemberGroups']) ? $sensorIni['AllowAdditionalMemberGroups'] == 'enabled' : true,
                        'ShowInboxAllPrivateMessage' => isset($sensorIni['ShowInboxAllPrivateMessage']) ? $sensorIni['ShowInboxAllPrivateMessage'] == 'enabled' : false,
                        'HasCategoryPredictor' => SensorCategoryPredictor::instance()->isEnabled(),
                        'SiteLanguages' => isset($sensorIni['SiteLanguages']) ? explode(',', $sensorIni['SiteLanguages']) : [],
                        'UploadMaxNumberOfImages' => $imagesAttribute instanceof eZContentClassAttribute ? $imagesAttribute->attribute(OCMultiBinaryType::MAX_NUMBER_OF_FILES_FIELD) : 0,
                        'UploadMaxNumberOfFiles' => $filesAttribute instanceof eZContentClassAttribute ? $filesAttribute->attribute(OCMultiBinaryType::MAX_NUMBER_OF_FILES_FIELD) : 0,
                        'ScenarioCache' => isset($sensorIni['ScenarioCache']) ? $sensorIni['ScenarioCache'] === 'enabled' : false,
                        'InBoxFirstApproverReadStrategy' => isset($sensorIni['InBoxFirstApproverReadStrategy']) ? $sensorIni['InBoxFirstApproverReadStrategy'] : 'by_group',
                        'AddPrivateMessageBeforeReassign' => isset($sensorIni['AddPrivateMessageBeforeReassign']) ? $sensorIni['AddPrivateMessageBeforeReassign'] == 'enabled' : false,
                        'CloseOnUserGroupPostFix' => isset($sensorIni['CloseOnUserGroupPostFix']) ? $sensorIni['CloseOnUserGroupPostFix'] == 'enabled' : false,
                        'RequireCategoryForAdditionalMemberGroups' => isset($sensorIni['RequireCategoryForAdditionalMemberGroups']) ? $sensorIni['RequireCategoryForAdditionalMemberGroups'] == 'enabled' : true,
                        'AddOperatorSuperUserAsObserver' => isset($sensorIni['AddOperatorSuperUserAsObserver']) ? $sensorIni['AddOperatorSuperUserAsObserver'] == 'enabled' : false,
                        'AddBehalfOfUserAsObserver' => isset($sensorIni['AddBehalfOfUserAsObserver']) ? $sensorIni['AddBehalfOfUserAsObserver'] == 'enabled' : true,
                        'HighlightSuperUserPosts' => isset($sensorIni['HighlightSuperUserPosts']) ? $sensorIni['HighlightSuperUserPosts'] == 'enabled' : false,
                        'UserCanAccessUserGroupPosts' => isset($sensorIni['HighlightSuperUserPosts']) ? $sensorIni['UserCanAccessUserGroupPosts'] == 'enabled' : false,
                        'DefaultPrivacyStatus' => isset($sensorIni['DefaultPrivacyStatus']) ? $sensorIni['DefaultPrivacyStatus'] : 'privacy.public',
                    );
                    return [
                        'content' => $data,
                        'scope' => 'sensor-settings',
                        'datatype' => 'php',
                        'store' => true
                    ];
                },
                null, null, [$modified]
            );

            $this->settings = new Settings($data);
        }

        return $this->settings;
    }

    public function getCurrentUser()
    {
        if ($this->user === null)
            $this->user = $this->getUserService()->loadUser(eZUser::currentUserID());

        return $this->user;
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
        return isset($this->data['post_class_data_map'][$identifier]) ? $this->data['post_class_data_map'][$identifier] : false;
    }

    public function getUserRootNode()
    {
        if (!isset($this->data['users']))
            $this->data['users'] = eZContentObjectTreeNode::fetch(intval(eZINI::instance()->variable("UserSettings", "DefaultUserPlacement")));
        return $this->data['users'];
    }

    public function getSensorPostStates($identifier)
    {
        $translations = SensorTranslationHelper::instance();
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
        TreeNode::clearCache(\eZINI::instance()->variable("UserSettings", "DefaultUserPlacement"));
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
                if ($notification instanceof NotificationType) {
                    $this->getNotificationService()->addUserToNotification($user, $notification);
                }
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
        $translations = SensorTranslationHelper::instance();
        $data = [];
        $data['default'] = [
            'uri' => 'sensor/config',
            'label' => $translations->translate('Settings'),
            'node' => false,
            'icon' => 'fa fa-cogs',
        ];
        $data['users'] = [
            'uri' => 'sensor/config/users',
            'label' => $translations->translate('Users'),
            'node' => false,
            'icon' => 'fa fa-user',
        ];
        if ($this->getSensorSettings()->get('AllowAdditionalMemberGroups')){
            $data['user_groups'] = [
                'uri' => 'sensor/config/user_groups',
                'label' => $translations->translate('User groups'),
                'node' => false,
                'icon' => 'fa fa-group',
            ];
        }
        $data['operators'] = [
            'uri' => 'sensor/config/operators',
            'label' => $translations->translate('Operators'),
            'node' => false,
            'icon' => 'fa fa-user-circle',
        ];
        $data['groups'] = [
            'uri' => 'sensor/config/groups',
            'label' => $translations->translate('Operator groups'),
            'node' => false,
            'icon' => 'fa fa-user-circle-o'
        ];
        $data['categories'] = [
            'uri' => 'sensor/config/categories',
            'label' => $translations->translate('Categories'),
            'node' => false,
            'icon' => 'fa fa-tags',
        ];
        $data['areas'] = [
            'uri' => 'sensor/config/areas',
            'label' => $translations->translate('Areas'),
            'node' => false,
            'icon' => 'fa fa-map-marker',
        ];

//        /** @var eZContentObjectTreeNode[] $otherFolders */
//        $otherFolders = (array)$this->getRootNode()->subTree(array(
//                'ClassFilterType' => 'include',
//                'ClassFilterArray' => array('folder'),
//                'Depth' => 1,
//                'DepthOperator' => 'eq')
//        );
//        foreach ($otherFolders as $folder) {
//            if (
//                $folder->attribute('contentobject_id') != $this->getCategoriesRootNode()->attribute('contentobject_id')
//                && $folder->attribute('contentobject_id') != $this->getGroupsRootNode()->attribute('contentobject_id')
//                && $folder->attribute('contentobject_id') != $this->getScenariosRootNode()->attribute('contentobject_id')
//                && $folder->attribute('contentobject_id') != $this->getFaqRootNode()->attribute('contentobject_id')
//                && ($this->getReportsRootNode() && $folder->attribute('contentobject_id') != $this->getReportsRootNode()->attribute('contentobject_id'))
//            ) {
//                $data['data-' . $folder->attribute('contentobject_id')] = [
//                    'uri' => 'sensor/config/' . 'data-' . $folder->attribute('contentobject_id'),
//                    'label' => $folder->attribute('name'),
//                    'node' => $folder,
//                    'icon' => 'fa fa-folder'
//                ];
//            }
//        }
        if (eZUser::currentUser()->hasAccessTo('*', '*')['accessWord'] == 'yes') {
            $data['automations'] = [
                'uri' => 'sensor/config/automations',
                'label' => $translations->translate('Automations'),
                'node' => false,
                'icon' => 'fa fa-android',
            ];
            if ($this->getReportsRootNode()) {
                $data['reports'] = [
                    'uri' => 'sensor/config/reports',
                    'label' => $translations->translate('Reports'),
                    'node' => false,
                    'icon' => 'fa fa-line-chart',
                ];
            }
            $data['translations'] = [
                'uri' => 'sensor/config/translations',
                'label' => $translations->translate('Translations'),
                'node' => false,
                'icon' => 'fa fa-language',
            ];
            $data['batch'] = [
                'uri' => 'sensor/config/batch',
                'label' => 'Batch operations',
                'node' => false,
                'icon' => 'fa fa-caret-square-o-right',
            ];
        }

        $data['notifications'] = [
            'uri' => 'sensor/config/notifications',
            'label' => $translations->translate('Notification texts'),
            'node' => false,
            'icon' => 'fa fa-align-left',
        ];

        $data['statistics'] = [
            'uri' => 'sensor/config/statistics',
            'label' => $translations->translate('Statistics'),
            'node' => false,
            'icon' => 'fa fa-pie-chart',
        ];

        $data['faq'] = [
            'uri' => 'sensor/config/faq',
            'label' => $translations->translate('Faq'),
            'node' => false,
            'icon' => 'fa fa-question-circle',
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

    public function getReportsRootNode()
    {
        if (!isset($this->data['reports'])) {
            $reportsObject = $this->fetchObjectRemoteID(self::sensorRootRemoteId() . '_reports');
            $this->data['reports'] = $reportsObject instanceof eZContentObject ? $reportsObject->attribute('main_node') : false;
        }
        return $this->data['reports'];
    }

    private function fetchObjectRemoteID($id)
    {
//        $storage = new FileSystem();
//        try {
//            $content = $storage->loadContent($id);
//            if ($content instanceof Content) {
//                return $content->getContentObject($this->getCurrentLanguage());
//            }
//        } catch (Exception $e) {
//
//        }
        return eZContentObject::fetchByRemoteID($id);
    }

    public function getFaqRootNode()
    {
        if (!isset($this->data['faq'])) {
            $this->data['faq'] = $this->fetchObjectRemoteID(self::sensorRootRemoteId() . '_faq')->attribute('main_node');
        }
        return $this->data['faq'];
    }

    public function getPublicPostContentClassAttributes()
    {
        if (!isset($this->data['post_class_data_map'])) {
            $this->data['post_class_data_map'] = $this->getPostContentClass()->dataMap();
        }
        $attributeList = [];
        foreach ($this->data['post_class_data_map'] as $identifier => $attribute) {
            if ($attribute->attribute('category') == 'content' || $attribute->attribute('category') == '') {

                if ($identifier == 'type' && $this->getSensorSettings()->get('HideTypeChoice')){
                    continue;
                }

                if ($identifier == 'privacy' && $this->getSensorSettings()->get('HidePrivacyChoice')){
                    continue;
                }

                $attributeList[$identifier] = $attribute;
            }
        }

        return $attributeList;
    }

    public function getMembersAvailableGroups()
    {
        if (!isset($this->treeCache['user-groups'])) {
            $this->treeCache['user-groups'] = [];
            $defaultUserPlacement = (int)\eZINI::instance()->variable("UserSettings", "DefaultUserPlacement");
            $defaultUserNode = eZContentObjectTreeNode::fetch($defaultUserPlacement);
            if ($defaultUserNode instanceof eZContentObjectTreeNode) {
                $userGroups = TreeNode::walk($defaultUserNode, ['classes' => ['user_group']]);
                foreach ($userGroups->attribute('children') as $item){
                    if ($this->getOperatorsRootNode()->attribute('contentobject_id') != $item->attribute('id')) {
                        $this->treeCache['user-groups'][$item->attribute('id')] = [
                            'name' => $item->attribute('name'),
                            'node_id' => $item->attribute('node_id'),
                        ];
                    }
                }
            }
        }

        return $this->treeCache['user-groups'];
    }
}
