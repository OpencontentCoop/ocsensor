<?php

use OpenContent\Sensor\Legacy\Repository as CoreRepository;
use OpenContent\Sensor\Utils\TreeNode;
use OpenContent\Sensor\Api\Exception\BaseException;
use OpenContent\Sensor\Core\PermissionDefinitions;
use OpenContent\Sensor\Core\ActionDefinitions;
use OpenContent\Sensor\Legacy\Values\Settings;

class OpenPaSensorRepository extends CoreRepository
{
    protected $data = array();

    protected function __construct()
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
        $permissionDefinitions[] = new PermissionDefinitions\CanRead();
        $permissionDefinitions[] = new PermissionDefinitions\CanRespond();
        $permissionDefinitions[] = new PermissionDefinitions\CanSendPrivateMessage();
        $permissionDefinitions[] = new PermissionDefinitions\CanSetExpiryDays();

        if ( $this->getSensorSettings()->offsetGet('ApproverCanReopen') )
            $permissionDefinitions[] = new PermissionDefinitions\CanReopen();

        $this->setPermissionDefinitions( $permissionDefinitions );

        $actionDefinitions = array();
        $actionDefinitions[] = new ActionDefinitions\ReadAction();
        $actionDefinitions[] = new ActionDefinitions\AssignAction();
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
            //@ svuotare cachce translations?
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
        if ( !isset($this->data['root']) )
            $this->data['root'] = eZContentObject::fetchByRemoteID( self::sensorRootRemoteId() )->attribute( 'main_node' );
        return $this->data['root'];
    }

    public function getOperatorsRootNode()
    {
        if ( !isset($this->data['operators']) )
            $this->data['operators'] = eZContentObject::fetchByRemoteID( self::sensorRootRemoteId() . '_operators' )->attribute( 'main_node' );
        return $this->data['operators'];
    }

    public function getCategoriesRootNode()
    {
        if ( !isset($this->data['categories']) )
            $this->data['categories'] = eZContentObject::fetchByRemoteID( self::sensorRootRemoteId() . '_postcategories' )->attribute( 'main_node' );
        return $this->data['categories'];
    }

    public function getAreasRootNode()
    {
        if ( !isset($this->data['areas']) )
            $this->data['areas'] = $this->getRootNode();
        return $this->data['areas'];
    }

    public function getOperatorContentClass()
    {
        if ( !isset($this->data['operator_class']) )
            $this->data['operator_class'] = eZContentClass::fetchByIdentifier( 'sensor_operator' );
        return $this->data['operator_class'];
    }

    public function getSensorCollaborationHandlerTypeString()
    {
        return 'openpasensor';
    }

    public function getPostRootNode()
    {
        if ( !isset($this->data['posts']) )
            $this->data['posts'] = eZContentObject::fetchByRemoteID( self::sensorRootRemoteId() . '_postcontainer' )->attribute( 'main_node' );
        return $this->data['posts'];
    }

    public function getPostContentClass()
    {
        if ( !isset($this->data['post_class']) )
            $this->data['post_class'] = eZContentClass::fetchByIdentifier( 'sensor_post' );
        return $this->data['post_class'];
    }

    public function getUserRootNode()
    {
        if ( !isset($this->data['users']) )
            $this->data['users'] = eZContentObjectTreeNode::fetch( intval( eZINI::instance()->variable( "UserSettings", "DefaultUserPlacement" ) ) );
        return $this->data['users'];
    }

    public function getSensorPostStates( $identifier )
    {
        if ( !isset( $this->data['states_' . $identifier] ) )
        {
            if ( $identifier == 'sensor' )
            {
                $this->data['states_sensor'] = OpenPABase::initStateGroup(
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
                $this->data['states_privacy'] = OpenPABase::initStateGroup(
                    'privacy',
                    array(
                        'public' => "Pubblico",
                        'private' => "Privato",
                    )
                );
            }
            elseif ( $identifier == 'moderation' )
            {
                $this->data['states_moderation'] = OpenPABase::initStateGroup(
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
        return $this->data['states_' . $identifier];
    }

}
