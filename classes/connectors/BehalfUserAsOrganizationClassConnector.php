<?php

use Opencontent\Sensor\Legacy\UserService;

class BehalfUserAsOrganizationClassConnector extends BehalfUserClassConnector
{
    public function getOptions()
    {
        $options = parent::getOptions();
        $options['fields']['user_type']['hideNone'] = true;
        return $options;
    }

    public function getSchema()
    {
        $schema = parent::getSchema();
        $schema['properties']['user_type']['default'] = UserService::USER_TYPES[1];
        $schema['properties']['user_type']['enum'] = [UserService::USER_TYPES[1]];
        unset($schema['properties']['fiscal_code']['pattern']);
        return $schema;
    }

    public function getSubmitData()
    {
        $data = $this->submitData;
        $data['user_type'] = UserService::USER_TYPES[1];
        return $data;
    }
}