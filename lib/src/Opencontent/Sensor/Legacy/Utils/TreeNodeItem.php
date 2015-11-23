<?php

namespace OpenContent\Sensor\Utils;

use eZContentObjectTreeNode;
use eZContentObjectAttribute;
use eZGmapLocation;

class TreeNodeItem
{
    /**
     * @var eZContentObjectTreeNode
     */
    protected $node;

    protected $geo;

    protected $parameters;

    public function __construct( eZContentObjectTreeNode $node, $parameters = array() )
    {
        $this->node = $node;
        $this->parameters = $parameters;
    }

    protected function geo()
    {
        /** @var eZContentObjectAttribute[] $dataMap */
        $dataMap = $this->node->attribute( 'data_map' );
        if ( isset( $dataMap['geo'] ) && $dataMap['geo']->hasContent() )
        {
            /** @var eZGmapLocation $content */
            $content = $dataMap['geo']->content() ;
            $data = array( 'lat' => $content->attribute( 'latitude' ), 'lng' => $content->attribute( 'longitude' ) );
            return array(
                'id' => $this->node->attribute( 'contentobject_id' ),
                'coords' => array(
                    $data['lat'],
                    $data['lng']
                )
            );
        }
        return null;
    }

    public function children()
    {
        $data = array();
        if ( $this->node->childrenCount( false ) > 0 )
        {
            if ( !$this->parameters['classes'] )
            {
                $children = $this->node->children();
            }
            else
            {
                $children = $this->node->subTree( array(
                    'Depth' => 1,
                    'DepthOperator' => 'eq',
                    'ClassFilterType' => 'include',
                    'ClassFilterArray' => $this->parameters['classes'],
                    'Limitation' => array(),
                    'SortBy' => $this->node->attribute( 'sort_array' )
                ) );
            }
            /** @var eZContentObjectTreeNode[] $children */
            foreach( $children as $child )
            {
                $data[] = new TreeNodeItem( $child, $this->parameters );
            }
        }
        return $data;
    }

    public function attributes()
    {
        return array(
            'node',
            'geo',
            'children'
        );
    }

    public function hasAttribute( $name )
    {
        return in_array( $name, $this->attributes() );
    }

    public function attribute( $name )
    {
        if ( $name == 'node' )
            return $this->node;
        elseif ( $name == 'geo' )
            return $this->geo;
        elseif( $name == 'children' )
            return $this->children();

        return false;
    }
}