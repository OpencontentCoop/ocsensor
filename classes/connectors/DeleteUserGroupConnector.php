<?php

class DeleteUserGroupConnector extends \Opencontent\Ocopendata\Forms\Connectors\DeleteObjectConnector
{
    protected function getSchema()
    {
        $schema = parent::getSchema();
        unset($schema['properties']['trash']);
        return $schema;
    }

    protected function submit()
    {
        $submit = parent::submit();
        $role = eZRole::fetchByName(UserGroupClassConnector::generateGroupRoleName($this->getHelper()->getParameter('object')));
        if ($role instanceof eZRole){
            $role->removeThis();
        }
        return $submit;
    }
}
