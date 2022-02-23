<?php

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\ClassConnector;
use Opencontent\Sensor\Legacy\Scenarios\SensorScenario;

class SensorScenarioClassConnector extends ClassConnector
{
    public function getSchema()
    {
        $schema = parent::getSchema();

        $properties = $schema['properties'];

        $schema['properties'] = [];

        $schema['properties']['triggers'] = $properties['triggers'];

        $schema['properties']['criterion_type'] = $properties['criterion_type'];
        $schema['properties']['criterion_category'] = $properties['criterion_category'];
        $schema['properties']['criterion_area'] = $properties['criterion_area'];
        $schema['properties']['criterion_reporter_group'] = $properties['criterion_reporter_group'];

        $schema['properties']['approver'] = $properties['approver'];
        $properties['reporter_as_approver']['title'] = '';
        $schema['properties']['reporter_as_approver'] = $properties['reporter_as_approver'];

        $schema['properties']['owner_group'] = $properties['owner_group'];
        $schema['properties']['owner'] = $properties['owner'];
        $properties['random_owner']['title'] = '';
        $schema['properties']['random_owner'] = $properties['random_owner'];
        $properties['reporter_as_owner']['title'] = '';
        $schema['properties']['reporter_as_owner'] = $properties['reporter_as_owner'];

        $schema['properties']['observer'] = $properties['observer'];
        $properties['reporter_as_observer']['title'] = '';
        $schema['properties']['reporter_as_observer'] = $properties['reporter_as_observer'];

        $schema["dependencies"] = [
            'approver' => ['reporter_as_approver'],
            'random_owner' => ['owner_group', 'owner'],
            'owner_group' => ['reporter_as_owner'],
            'owner' => ['reporter_as_owner'],
        ];

        $schema['properties']['expiry'] = $properties['expiry'];
        $schema['properties']['category'] = $properties['category'];

        return $schema;
    }

    public function getOptions()
    {
        $options = parent::getOptions();

        $options['fields']['approver']['dependencies']['reporter_as_approver'] = false;
        $options['fields']['owner_group']['dependencies']['reporter_as_owner'] = false;
        $options['fields']['owner']['dependencies']['reporter_as_owner'] = false;
        $options['fields']['random_owner']['dependencies']['owner'] = '';
        $options['fields']['expiry']['type'] = 'integer';

        return $options;
    }

    public function submit()
    {
        $submitData = $this->getSubmitData();
        $hasAssignment = false;
        foreach ($submitData as $key => $value){
            if (
                strpos($key, 'criterion_') === false
                && !in_array($key, ['triggers'])
                && $value
                && $value !== 'false'
            ){
                $hasAssignment = true;
            }
        }

        if (!$hasAssignment){
            throw new Exception("Seleziona almeno un'assegnazione");
        }

        $remoteId = SensorScenario::generateRemoteId($submitData);
        $alreadyExists = eZContentObject::fetchByRemoteID($remoteId);
        if ($alreadyExists instanceof eZContentObject) {
            $alreadyExistsId = $alreadyExists->attribute('id');
            if ($this->getHelper()->hasParameter('object')) {
                $current = eZContentObject::fetch((int)$this->getHelper()->getParameter('object'));
                if ($current instanceof eZContentObject && $current->attribute('remote_id') !== $remoteId) {
                    throw new Exception("Esiste già uno scenario con i criteri selezionati (#{$alreadyExistsId})");
                }
            }else{
                throw new Exception("Esiste già uno scenario con i criteri selezionati (#{$alreadyExistsId})");
            }
        }

        $payload = $this->getPayloadFromArray($submitData);

        $result = $this->doSubmit($payload);

        if ($result['message'] == 'success'){
            $id = (int)$result['content']['metadata']['id'];
            eZContentObject::clearCache([$id]);
            $object = eZContentObject::fetch($id);
            if ($object instanceof eZContentObject && $object->attribute('remote_id') !== $remoteId){
                $object->setAttribute('remote_id', $remoteId);
                $object->store();
            }
        }

        return $result;
    }


}
