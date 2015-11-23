<?php

namespace OpenContent\Sensor\Utils;

use eZContentObjectTreeNode;

class TreeNode
{
    public static function walk( eZContentObjectTreeNode $node, $parameters = array() )
    {
        if ( !isset( $parameters['classes'] ) )
            $parameters['classes'] = null;

        return new TreeNodeItem( $node, $parameters );
    }

}