<?php

class SensorAreaCsvExporter extends CSVExporter
{
    private $rootId;

    public function __construct()
    {
        $this->rootId = OpenPaSensorRepository::instance()->getAreasRootNode()->attribute('main_node_id');
        $classIdentifier = 'sensor_area';
        parent::__construct($this->rootId, $classIdentifier);
        $this->CSVheaders['id'] = 'ID';
        $this->CSVheaders['name'] = 'Nome';
    }

    function transformNode( eZContentObjectTreeNode $node )
    {
        $values = array();
        if ($node instanceof eZContentObjectTreeNode) {
            $values['id'] = $node->attribute('contentobject_id');
            $values['name'] = $node->attribute('name');
        }
        return $values;
    }
}