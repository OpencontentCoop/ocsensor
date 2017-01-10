<?php

class GroupHelper
{

    public static $referenceGroupStateGroupIdentifier = 'reference_group';

    /**
     * @param eZContentObject $object
     *
     * @throws Exception
     */
    public static function createGroup( eZContentObject $object )
    {
        if (!$object instanceof eZContentObject) {
            throw new Exception("Object not found");
        }

        // Creo lo stato per il gruppo
        $referenceGroupStates = OpenPABase::initStateGroup(
            ObjectHandlerServiceControlSensor::$referenceGroupStateIdentifier,
            array(
                'group_' . $object->ID => $object->Name
            )
        );

        $roles = array(
            "Sensor operators group $object->Name" => array(
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'read',
                    'Limitation' => array(
                        'Class' => eZContentClass::classIDByIdentifier( eZINI::instance( 'ocsensor.ini' )->variable( 'SensorConfig', 'SensorPostContentClasses' ) ),
                        'StateGroup_reference_group' => $referenceGroupStates['reference_group.group_' . $object->ID]->attribute( 'id' ),
                    )
                )
            )
        );
        self::installRoles($object, $roles);
    }

    /**
     * @param $groupObject
     * @param $roles
     * @throws Exception
     */
    protected static function installRoles( $groupObject, $roles )
    {
        foreach( $roles as $roleName => $policies )
        {
            $role = OpenPABase::initRole( $roleName, $policies, true );
            if ( !$role instanceof eZRole )
            {
                throw new Exception( "Error: problem with roles" );
            }
            $role->assignToUser( $groupObject->ID );
        }
    }

}