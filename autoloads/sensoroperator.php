<?php

use Opencontent\Opendata\Api\ClassRepository;

class SensorOperator
{
    function operatorList()
    {
        return array(
            'sensor_collaboration_identifier',
            'sensor_postcontainer',
            'sensor_categorycontainer',
            'sensor_categories',
            'sensor_areas',
            'sensor_default_approvers',
            'sensor_settings',
            'sensor_config_menu',
            'sensor_types',
            'sensor_is_moderation_enabled',
            'sensor_channels',
            'sensor_post_class',
            'sensor_operators',
            'sensor_groups',
            'sensor_posts_date_range',
            'sensor_statuses',
            'sensor_additional_map_layers',
            'sensor_faqcontainer',
        );
    }

    function namedParameterPerOperator()
    {
        return true;
    }

    function namedParameterList()
    {
        return array(
            'sensor_settings' => array(
                'setting' => array(
                    'type' => 'string',
                    'required' => false,
                    'default' => false,
                )
            ),
            'sensor_statuses' => array(
                'group' => array(
                    'type' => 'string',
                    'required' => false,
                    'default' => 'sensor',
                )
            ),
        );
    }

    function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters )
    {
        $repository = OpenPaSensorRepository::instance();
        switch ( $operatorName )
        {
            case 'sensor_faqcontainer':
                return $operatorValue = $repository->getFaqRootNode();
                break;

            case 'sensor_additional_map_layers':
                $additionalLayers = [];
                $sensorPostRoot = $repository->getPostRootNode();
                $sensorPostRootDataMap = $sensorPostRoot->dataMap();
                if (isset($sensorPostRootDataMap['additional_map_layers']) && $sensorPostRootDataMap['additional_map_layers']->hasContent()){
                    /** @var \eZMatrix $additionalLayersMatrix */
                    $additionalLayersMatrix = $sensorPostRootDataMap['additional_map_layers']->content();
                    if ($additionalLayersMatrix instanceof eZMatrix) {
                        $columns = (array)$additionalLayersMatrix->attribute('columns');
                        $rows = (array)$additionalLayersMatrix->attribute('rows');
                        $keys = array();
                        foreach ($columns['sequential'] as $column) {
                            $keys[] = $column['identifier'];
                        }
                        foreach ($rows['sequential'] as $row) {
                            $additionalLayers[] = array_combine($keys, $row['columns']);
                        }
                    }
                }elseif (eZINI::instance('ocsensor.ini')->hasVariable('GeoCoderSettings', 'AdditionalMapLayers')){
                    foreach (eZINI::instance('ocsensor.ini')->variable('GeoCoderSettings', 'AdditionalMapLayers') as $layer){
                        $parts = explode('|', $layer);
                        $additionalLayers[] = [
                            'baseUrl' =>  $parts[0],
                            'version' =>  $parts[1],
                            'layers' =>  $parts[2],
                            'format' =>  $parts[3],
                            'transparent' => $parts[4] == 'true',
                            'attribution' => $parts[5],
                        ];
                    }
                }
                $operatorValue = $additionalLayers;
                break;

            case 'sensor_statuses':
                $operatorValue = $repository->getSensorPostStates($namedParameters['group']);
                break;

            case 'sensor_groups':
                $operatorValue = $repository->getGroupsTree();
                break;

            case 'sensor_operators':
                $operatorValue = $repository->getOperatorsTree();
                break;

            case 'sensor_post_class':
            {
                $classRepository = new ClassRepository();
                $operatorValue = (array)$classRepository->load($repository->getPostContentClassIdentifier());
                break;
            }

            case 'sensor_posts_date_range':
            {
                $first = $repository->getSearchService()->searchPosts('sort [published=>asc] limit 1', [], []);
                if ($first->totalCount > 0){
                    $first = $first->searchHits[0];
                    $last = $repository->getSearchService()->searchPosts('sort [published=>desc] limit 1', [], [])->searchHits[0];
                    /** @var \Opencontent\Sensor\Api\Values\Post $first */
                    /** @var \Opencontent\Sensor\Api\Values\Post $last */
                    $operatorValue = [
                        'first' => $first->published->setTime(0,0)->format('c'),
                        'last' => $last->published->setTime(23,0)->format('c')
                    ];
                }else{
                    $operatorValue = [
                        'first' => (new DateTime())->setTime(0,0)->format('c'),
                        'last' => (new DateTime())->setTime(23,0)->format('c')
                    ];
                }
                break;
            }

            case 'sensor_channels':
            {
                $channels = [];
                foreach ($repository->getChannelService()->loadPostChannels() as $channel){
                    $channels[] = $channel->name;
                }
                $operatorValue = $channels;
                break;
            }

            case 'sensor_is_moderation_enabled':
            {
                $operatorValue = $repository->isModerationEnabled();
                break;
            }

            case 'sensor_types':
            {
                $operatorValue = $repository->getPostTypeService()->loadPostTypes();
                break;
            }

            case 'sensor_config_menu':
            {
                $operatorValue = $repository->getConfigMenu();
                break;
            }

            case 'sensor_settings':
                $settings = $repository->getSensorSettings()->jsonSerialize();
                if ($namedParameters['setting'] === false){
                    return $operatorValue = $settings;
                }else{
                    return $operatorValue = $settings[$namedParameters['setting']];
                }
                break;

            case 'sensor_default_approvers':
            {
                $scenario = new \Opencontent\Sensor\Legacy\Scenarios\FirstAreaApproverScenario($repository);
                $ids = $scenario->getApprovers();
                $ids = array_map('intval', $ids);
                $data = array();
                if ( !empty( $ids ) ){
                    $data = eZContentObject::fetchList( true, array( 'id' => array( $ids ) ) );
                }
                return $operatorValue = $data;
            } break;

            case 'sensor_collaboration_identifier':
            {
                return $operatorValue = $repository->getSensorCollaborationHandlerTypeString();
            } break;


            case 'sensor_postcontainer':
            {
                return $operatorValue = $repository->getPostRootNode();
            } break;

            case 'sensor_categorycontainer':
            {
                return $operatorValue = $repository->getCategoriesRootNode();
            } break;

            case 'sensor_areas':
                $operatorValue = $repository->getAreasTree();
            break;

            case 'sensor_categories':
                $operatorValue = $repository->getCategoriesTree();
            break;

        }
        return null;
    }
} 