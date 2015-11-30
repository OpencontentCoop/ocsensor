<?php

use OpenContent\Sensor\Legacy\Repository as CoreRepository;
use OpenContent\Sensor\Utils\TreeNode;
use OpenContent\Sensor\Api\Exception\BaseException;
use OpenContent\Sensor\Core\PermissionDefinitions;
use OpenContent\Sensor\Legacy\Values\Settings;

class OpenPaSensorRepository extends CoreRepository
{
    protected $statesByIdentifier = array();

    public function __construct()
    {
        $permissionDefinitions = array();
        $permissionDefinitions[] = new PermissionDefinitions\CanAddArea();
        $permissionDefinitions[] = new PermissionDefinitions\CanAddCategory();
        $permissionDefinitions[] = new PermissionDefinitions\CanAddObserver();
        $permissionDefinitions[] = new PermissionDefinitions\CanAssign();
        $permissionDefinitions[] = new PermissionDefinitions\CanChangePrivacy();
        $permissionDefinitions[] = new PermissionDefinitions\CanClose();
        $permissionDefinitions[] = new PermissionDefinitions\CanComment();
        $permissionDefinitions[] = new PermissionDefinitions\CanFix();
        $permissionDefinitions[] = new PermissionDefinitions\CanForceFix();
        $permissionDefinitions[] = new PermissionDefinitions\CanModerate();
        if ( $this->getSensorSettings()->offsetGet('ApproverCanReopen') )
            $permissionDefinitions[] = new PermissionDefinitions\CanReopen();
        $permissionDefinitions[] = new PermissionDefinitions\CanRespond();
        $permissionDefinitions[] = new PermissionDefinitions\CanSendPrivateMessage();
        $permissionDefinitions[] = new PermissionDefinitions\CanSetExpiryDays();

        $this->setPermissionDefinitions( $permissionDefinitions );

        $actionDefinitions = array();
        $this->setActionDefinitions( $actionDefinitions );
    }

    public static function sensorRootRemoteId()
    {
        return OpenPABase::getCurrentSiteaccessIdentifier() . '_openpa_sensor';
    }

    public function getSensorSettings()
    {
        //@todo
        return new Settings( array() );
    }

    public function getCurrentUser()
    {
        if ( $this->user === null )
            $this->user = $this->getUserService()->loadUser( eZUser::currentUserID() );
        return $this->user;
    }

    public function setCurrentLanguage( $language )
    {
        $this->language = $language;
        if ( $this->language != eZLocale::currentLocaleCode() )
        {
            //@todo
            //$GLOBALS["eZLocaleStringDefault"] = $this->language;
            //@ svuotare cahce translations?
        }
    }

    public function getCurrentLanguage()
    {
        if ( $this->language === null )
            return eZLocale::currentLocaleCode();

        return $this->language;
    }

    public function getRootNode()
    {
        return eZContentObject::fetchByRemoteID( self::sensorRootRemoteId() )->attribute( 'main_node' );
    }

    public function getOperatorsRootNode()
    {
        return eZContentObject::fetchByRemoteID( self::sensorRootRemoteId() . '_operators' )->attribute( 'main_node' );
    }

    public function getCategoriesRootNode()
    {
        return eZContentObject::fetchByRemoteID( self::sensorRootRemoteId() . '_postcategories' )->attribute( 'main_node' );
    }

    public function getAreasRootNode()
    {
        return $this->getRootNode();
    }

    public function getOperatorContentClass()
    {
        return eZContentClass::fetchByIdentifier( 'sensor_operator' );
    }

    public function getSensorCollaborationHandlerTypeString()
    {
        return 'openpasensor';
    }

    public function getPostRootNode()
    {
        return eZContentObject::fetchByRemoteID( self::sensorRootRemoteId() . '_postcontainer' )->attribute( 'main_node' );
    }

    public function getPostContentClass()
    {
        return eZContentClass::fetchByIdentifier( 'sensor_post' );
    }

    public function getUserRootNode()
    {
        return eZContentObjectTreeNode::fetch( intval( eZINI::instance()->variable( "UserSettings", "DefaultUserPlacement" ) ) );
    }

    public function getSensorPostStates( $identifier )
    {
        if ( !isset( $this->statesByIdentifier[$identifier] ) )
        {
            if ( $identifier == 'sensor' )
            {
                $this->statesByIdentifier['sensor'] = OpenPABase::initStateGroup(
                    'sensor',
                    array(
                        'pending' => "Inviato",
                        'open' => "In carico",
                        'close' => "Chiusa"
                    )
                );
            }
            elseif ( $identifier == 'privacy' )
            {
                $this->statesByIdentifier['privacy'] = OpenPABase::initStateGroup(
                    'privacy',
                    array(
                        'public' => "Pubblico",
                        'private' => "Privato",
                    )
                );
            }
            elseif ( $identifier == 'moderation' )
            {
                $this->statesByIdentifier['moderation'] = OpenPABase::initStateGroup(
                    'moderation',
                    array(
                        'skipped' => "Non necessita di moderazione",
                        'waiting' => "In attesa di moderazione",
                        'accepted' => "Accettato",
                        'refused' => "Rifiutato"
                    )
                );
            }
            else
            {
                throw new BaseException( "Status $identifier not handled" );
            }
        }
        return $this->statesByIdentifier[$identifier];
    }

}
