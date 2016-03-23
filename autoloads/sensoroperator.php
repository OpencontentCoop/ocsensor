<?php

class SensorOperator
{
    function operatorList()
    {
        return array(
            'sensor_collaboration_identifier',
            'sensor_root_handler',
            'sensor_post',
            'sensor_postcontainer',
            'sensor_categorycontainer',
            'sensor_chart_list',
            'sensor_categories',
            'sensor_areas',
            'sensor_default_approvers'
        );
    }

    function namedParameterPerOperator()
    {
        return true;
    }

    function namedParameterList()
    {
        return array();
    }

    function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters )
    {
        switch ( $operatorName )
        {
            case 'sensor_default_approvers':
            {
                $ids = SensorHelper::defaultApproverIdArray();
                $ids = array_map('intval', $ids);
                $data = array();
                if ( !empty( $ids ) ){
                    $data = eZContentObject::fetchList( true, array( 'id' => array( $ids ) ) );
                }
                return $operatorValue = $data;
            } break;

            case 'sensor_collaboration_identifier':
            {
                return $operatorValue = SensorHelper::factory()->getSensorCollaborationHandlerTypeString();
            } break;

            case 'sensor_root_handler':
            {
                return $operatorValue = ObjectHandlerServiceControlSensor::rootHandler();
            } break;

            case 'sensor_post':
            {
                if ( $operatorValue instanceof eZContentObject )
                {
                    try
                    {
                        $operatorValue = SensorHelper::instanceFromContentObjectId(
                            $operatorValue->attribute( 'id' )
                        );
                    }
                    catch( Exception $e )
                    {
                        eZDebug::writeError( $e->getMessage(), __METHOD__ );
                        $operatorValue = null;
                    }
                }
            } break;

            case 'sensor_postcontainer':
            {
                return $operatorValue = SensorHelper::postContainerNode();
            } break;

            case 'sensor_categorycontainer':
            {
                return $operatorValue = SensorHelper::postCategoriesNode();
            } break;

            case 'sensor_chart_list':
                $operatorValue = SensorCharts::listAvailableCharts();
                break;

            case 'sensor_areas':
                $operatorValue = OpenPaSensorRepository::instance()->getAreasTree();
            break;

            case 'sensor_categories':
                $operatorValue = OpenPaSensorRepository::instance()->getCategoriesTree();
            break;

        }
        return null;
    }
} 