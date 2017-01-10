<?php

class CategoryHelper
{


    /**
     * @param eZContentObject $object
     *
     * @throws Exception
     */
    public static function createGroupFromCategory( eZContentObject $object )
    {
        if (!$object instanceof eZContentObject) {
            throw new Exception("Object not found");
        }
        $root = SensorHelper::rootNode();

        $parentGroupObject = eZContentObject::fetchByRemoteID( $root->ContentObject->RemoteID . '_operators' );
        if ( !$parentGroupObject instanceof eZContentObject )
        {
            throw new Exception("Parent group object not found");
        }

        // Verifico / creo il gruppo di riferimento per la categoria
        $groupObject = eZContentObject::fetchByRemoteID( $root->ContentObject->RemoteID . '_group_area_' . $object->attribute( 'id') );
        if ( !$groupObject instanceof eZContentObject )
        {
            // Operator group
            OpenPALog::warning( "Install $object->Name group" );
            $params = array(
                'parent_node_id' => $groupObject->attribute( 'main_node_id' ),
                'section_id' => $parentGroupObject->attribute( 'section_id' ),
                'remote_id' => $root->ContentObject->RemoteID . '_group_area_' . $object->attribute( 'id'),
                'class_identifier' => 'user_group',
                'attributes' => array(
                    'name' => 'Operatori $object->Name'
                )
            );
            /** @var eZContentObject $groupObject */
            $groupObject = eZContentFunctions::createAndPublishObject( $params );
            if( !$groupObject instanceof eZContentObject )
            {
                throw new Exception( 'Failed creating Sensor group node' );
            }
        }
    }
}