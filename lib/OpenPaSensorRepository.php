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
        $permissions = array();
        $permissions[] = new PermissionDefinitions\CanAddArea();
        $permissions[] = new PermissionDefinitions\CanAddCategory();
        $permissions[] = new PermissionDefinitions\CanAddObserver();
        $permissions[] = new PermissionDefinitions\CanAssign();
        $permissions[] = new PermissionDefinitions\CanChangePrivacy();
        $permissions[] = new PermissionDefinitions\CanClose();
        $permissions[] = new PermissionDefinitions\CanComment();
        $permissions[] = new PermissionDefinitions\CanFix();
        $permissions[] = new PermissionDefinitions\CanForceFix();
        $permissions[] = new PermissionDefinitions\CanModerate();
        if ( $this->getSensorSettings()->offsetGet('ApproverCanReopen') )
            $permissions[] = new PermissionDefinitions\CanReopen();
        $permissions[] = new PermissionDefinitions\CanRespond();
        $permissions[] = new PermissionDefinitions\CanSendPrivateMessage();
        $permissions[] = new PermissionDefinitions\CanSetExpiryDays();

        $this->setPermissionDefinitions( $permissions );
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
