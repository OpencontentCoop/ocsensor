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

            'sensor_datetime',
            'sensor_areas',
            'sensor_categories'
        );
    }

    function namedParameterPerOperator()
    {
        return true;
    }

    function namedParameterList()
    {
        return array(
            'sensor_datetime' => array(
                'action' => array( 'type' => 'string', 'required' => true ),
                'value' => array( 'type' => 'mixed', 'required' => true ),
            ),
        );
    }

    function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters )
    {
        switch ( $operatorName )
        {
            case 'sensor_datetime':
            {
                $date = $operatorValue;
                if ( $date instanceof DateTime )
                {
                    $action = $namedParameters['action'];
                    $value = $namedParameters['value'];

                    if ( $action == 'format' )
                    {
                        $locale = eZLocale::instance();
                        $function = $locale->getFormattingFunction( $value );
                        if ( $function )
                        {
                            $operatorValue = $locale->{$function}( $date->format( 'U' ) );
                        }
                        else
                        {
                            $operatorValue = $date->format( 'U' );
                        }

                    }
                    elseif( $action = 'gt' && $value instanceof DateTime )
                    {
                        $operatorValue = $date > $value;
                    }
                    elseif( $action = 'ge' && $value instanceof DateTime )
                    {
                        $operatorValue = $date >= $value;
                    }
                    elseif( $action = 'lt' && $value instanceof DateTime )
                    {
                        $operatorValue = $date < $value;
                    }
                    elseif( $action = 'le' && $value instanceof DateTime )
                    {
                        $operatorValue = $date <= $value;
                    }
                    elseif( $action = 'eq' && $value instanceof DateTime )
                    {
                        $operatorValue = $date == $value;
                    }
                }
            } break;

            case 'sensor_areas':
            {
                return $operatorValue = OpenPaSensorRepository::instance()->getAreasTree();
            } break;

            case 'sensor_categories':
            {
                return $operatorValue = OpenPaSensorRepository::instance()->getCategoriesTree();
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
                return $operatorValue = ObjectHandlerServiceControlSensor::postContainerNode();
            } break;

            case 'sensor_categorycontainer':
            {
                return $operatorValue = ObjectHandlerServiceControlSensor::postCategoriesNode();
            } break;
        }
        return null;
    }
} 