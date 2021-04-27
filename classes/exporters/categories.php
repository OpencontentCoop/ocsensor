<?php

class SensorCategoryCsvExporter extends CSVExporter
{
    private $rootId;

    public function __construct()
    {
        $this->rootId = OpenPaSensorRepository::instance()->getCategoriesRootNode()->attribute('main_node_id');
        $classIdentifier = 'sensor_category';
        parent::__construct($this->rootId, $classIdentifier);
        $this->CSVheaders['id'] = 'ID';
        $this->CSVheaders['name'] = 'Nome';
        $this->CSVheaders['parent'] = 'Macrocategoria';
    }

    function transformNode( eZContentObjectTreeNode $node )
    {
        $values = array();
        if ($node instanceof eZContentObjectTreeNode) {
            $values['id'] = $node->attribute('contentobject_id');
            $values['name'] = $node->attribute('name');
            if ($node->attribute('parent_node_id') !== $this->rootId){
                $values['parent'] = $node->attribute('parent')->attribute('name');
            }else{
                $values['parent'] = '';
            }
        }
        return $values;
    }
}