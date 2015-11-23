<?php

use OpenContent\Sensor\Legacy\Repository;
use OpenContent\Sensor\Utils\TreeNode;
use OpenContent\Sensor\Api\Exception\BaseException;

class OpenPaSensorRepository extends Repository
{
    protected $statesByIdentifier = array();

    public static function sensorRootRemoteId()
    {
        return OpenPABase::getCurrentSiteaccessIdentifier() . '_openpa_sensor';
    }

    public function getSensorSettings()
    {
        //@todo
        return array();
    }

    public function getCurrentUser()
    {
        //@todo
        return $this->user;
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

    public function setActionDefinitions( $actionDefinitions )
    {
        // TODO: Implement setActionDefinitions() method.
    }

    public function setPermissionDefinitions( $permissionDefinitions )
    {
        // TODO: Implement setPermissionDefinitions() method.
    }
}