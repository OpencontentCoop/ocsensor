<?php

class SensorHttpActionHelper
{
    protected $httpActions = array();
    /**
     * @var SensorUserPostRoles
     */
    protected $postUserRoles;

    protected function __construct( SensorUserPostRoles $postUserRoles )
    {
        $this->postUserRoles = $postUserRoles;

        $this->httpActions = array(
            'Assign' => array(
                'http_parameters' => array(
                    'SensorItemAssignTo' => array(
                        'required' => true,
                        'action_parameter_name' => 'participant_ids'
                    )
                ),
                'action_name' => 'assign',
            ),
            'Fix' => array(
                'action_name' => 'fix'
            ),
            'ForceFix' => array(
                'action_name' => 'force_fix'
            ),
            'Close' => array(
                'action_name' => 'close'
            ),
            'Reopen' => array(
                'action_name' => 'reopen'
            ),
            'MakePrivate' => array(
                'action_name' => 'make_private'
            ),
            'MakePublic' => array(
                'action_name' => 'make_public'
            ),
            'Moderate' => array(
                'http_parameters' => array(
                    'SensorItemModerationIdentifier' => array(
                        'required' => false,
                        'default' => 'accepted',
                        'action_parameter_name' => 'status'
                    )
                ),
                'action_name' => 'moderate',
            ),
            'AddObserver' => array(
                'http_parameters' => array(
                    'SensorItemAddObserver' => array(
                        'required' => true,
                        'action_parameter_name' => 'participant_ids'
                    )
                ),
                'action_name' => 'add_observer',
            ),
            'AddCategory' => array(
                'http_parameters' => array(
                    'SensorItemCategory' => array(
                        'required' => true,
                        'action_parameter_name' => 'category_id'
                    ),
                    'SensorItemAssignToCategoryApprover' => array(
                        'required' => false,
                        'default' => false,
                        'action_parameter_name' => 'assign_to_approver'
                    )
                ),
                'action_name' => 'add_category',
            ),
            'AddArea' => array(
                'http_parameters' => array(
                    'SensorItemArea'  => array(
                        'required' => true,
                        'action_parameter_name' => 'area_id'
                    )
                ),
                'action_name' => 'add_area',
            ),
            'AddEnteType' => array(
                'http_parameters' => array(
                    'SensorItemEnteType'  => array(
                        'required' => true,
                        'action_parameter_name' => 'ente_type_id'
                    )
                ),
                'action_name' => 'add_ente_type',
            ),
            'SetExpiry' => array(
                'http_parameters' => array(
                    'SensorItemExpiry'  => array(
                        'required' => true,
                        'action_parameter_name' => 'expiry_days'
                    )
                ),
                'action_name' => 'set_expiry',
            ),
            'Comment' => array(
                'http_parameters' => array(
                    'SensorItemComment'  => array(
                        'required' => true,
                        'action_parameter_name' => 'text'
                    )
                ),
                'action_name' => 'add_comment',
            ),
            'PrivateMessage' => array(
                'http_parameters' => array(
                    'SensorItemPrivateMessage'  => array(
                        'required' => true,
                        'action_parameter_name' => 'text'
                    ),
                    'SensorItemPrivateMessageReceiver' => array(
                        'required' => true,
                        'action_parameter_name' => 'participant_ids'
                    )
                ),
                'action_name' => 'add_message',
            ),
            'Respond' => array(
                'http_parameters' => array(
                    'SensorItemResponse' => array(
                        'required' => true,
                        'action_parameter_name' => 'text'
                    )
                ),
                'action_name' => 'add_response',
            ),
            'Attach' => array(
                'http_parameters' => array(
                    'SensorItemAttach' => array(
                        'required' => true,
                        'type' => 'file',
                        'action_parameter_name' => 'file'
                    )
                ),
                'action_name' => 'add_attachment',
            ),
            'RemoveAttach' => array(
                'http_parameters' => array(
                    'SensorItemRemoveAttach' => array(
                        'required' => true,
                        'action_parameter_name' => 'filename'
                    )
                ),
                'action_name' => 'remove_attachment',
            ),
            'EditComment' => array(
                'http_parameters' => array(
                    'SensorEditComment' => array(
                        'required' => true,
                        'action_parameter_name' => 'id_text'
                    )
                ),
                'action_name' => 'edit_comment',
            ),
            'EditMessage' => array(
                'http_parameters' => array(
                    'SensorEditMessage' => array(
                        'required' => true,
                        'action_parameter_name' => 'id_text'
                    )
                ),
                'action_name' => 'edit_message',
            ),
            'EditResponse' => array(
                'http_parameters' => array(
                    'SensorEditResponse' => array(
                        'required' => true,
                        'action_parameter_name' => 'id_text'
                    )
                ),
                'action_name' => 'edit_response',
            )
        );
    }

    final public static function instance( SensorUserPostRoles $postUserRoles )
    {
        //@todo customize handler
        return new SensorHttpActionHelper( $postUserRoles );
    }

    public function handleHttpAction( eZModule $module )
    {
        $http = eZHTTPTool::instance();
        foreach( $this->httpActions as $action => $parameters )
        {
            $actionPostVariable = 'CollaborationAction_' . $action;
            if ( $http->hasPostVariable( $actionPostVariable ) )
            {
                $actionName = $parameters['action_name'];
                $actionParameters = array();
                $doAction = true;
                if ( !isset( $parameters['http_parameters'] ) )
                {
                    $parameters['http_parameters'] = array();
                }
                foreach( $parameters['http_parameters'] as $parameterName => $parameterOptions )
                {
                    $parameterPostVariable = 'Collaboration_' . $parameterName;
                    $parameterPostType = isset($parameterOptions['type']) ? $parameterOptions['type'] : 'default';
                    $hasPost = $parameterPostType == 'file' ?
                        eZHTTPFile::canFetch($parameterPostVariable) :
                        $http->hasPostVariable( $parameterPostVariable );

                    if ( $parameterOptions['required'] && $hasPost )
                    {
                        $actionParameters[$parameterOptions['action_parameter_name']] = $parameterPostType == 'file' ? eZHTTPFile::fetch($parameterPostVariable) : $http->postVariable( $parameterPostVariable );
                    }
                    elseif ( isset( $parameterOptions['default'] ) )
                    {
                        $actionParameters[$parameterOptions['action_parameter_name']] = $parameterOptions['default'];
                    }
                    else
                    {
                        $doAction = false;
                        eZDebug::writeError( "Parameter $parameterName is required", $actionName );
                        break;
                    }
                }
                if ( $doAction )
                {
                    eZDebugSetting::writeNotice( 'sensor', "Http call $actionName action with arguments " . var_export( $actionParameters, 1 ), __METHOD__ );
                    try
                    {
                        $this->postUserRoles->handleAction( $actionName, $actionParameters );
                    }
                    catch( Exception $e )
                    {
                        eZDebugSetting::writeError( 'sensor', $e->getMessage(), __METHOD__ );
                        $this->postUserRoles->getUserInfo()->addFlashAlert( $e->getMessage(), 'error' );
                    }
                }
            }
        }
    }

}