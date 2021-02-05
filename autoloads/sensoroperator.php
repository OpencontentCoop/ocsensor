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
        );
    }

    function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters )
    {
        $repository = OpenPaSensorRepository::instance();
        switch ( $operatorName )
        {
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
                $scenario = new \Opencontent\Sensor\Legacy\PostService\Scenarios\FirstAreaApproverScenario($repository);
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