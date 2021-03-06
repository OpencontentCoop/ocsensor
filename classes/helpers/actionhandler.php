<?php

class SensorPostActionHandler
{
    /**
     * @var SensorUserPostRoles
     */
    protected $userPostRoles;

    /**
     * @var SensorPost
     */
    protected $post;

    /**
     * @var SensorUserInfo
     */
    protected $userInfo;

    protected $actions = array();

    protected function __construct( SensorUserPostRoles $userPostRoles )
    {
        $this->userPostRoles = $userPostRoles;
        $this->post = $this->userPostRoles->getPost();
        $this->userInfo = $this->userPostRoles->getUserInfo();

        $this->actions = array(
            'read' => array(
                'call_function' => 'read',
                'parameters' => array()
            ),
            'assign' => array(
                'call_function' => 'assign',
                'check_role' => array( 'can_assign' ),
                'parameters' => array(
                    'participant_ids' => array(
                        'required' => true
                    )
                )
            ),
            'fix' => array(
                'call_function' => 'fix',
                'check_role' => array( 'can_fix' ),
                'parameters' => array()
            ),
            'force_fix' => array(
                'call_function' => 'forceFix',
                'check_role' => array( 'can_force_fix' ),
                'parameters' => array()
            ),
            'close' => array(
                'call_function' => 'close',
                'check_role' => array( 'can_close' ),
                'parameters' => array()
            ),
            'reopen' => array(
                'call_function' => 'reopen',
                'check_role' => array( 'can_reopen' ),
                'parameters' => array()
            ),
            'make_private' => array(
                'call_function' => 'makePrivate',
                'check_role' => array( 'can_change_privacy' ),
                'parameters' => array()
            ),
            'make_public' => array(
                'call_function' => 'makePublic',
                'check_role' => array( 'can_change_privacy' ),
                'parameters' => array()
            ),
            'moderate' => array(
                'call_function' => 'moderate',
                'check_role' => array( 'can_moderate' ),
                'parameters' => array(
                    'status' => array(
                        'required' => true
                    )
                )
            ),
            'add_observer' => array(
                'call_function' => 'addObserver',
                'check_role' => array( 'can_add_observer' ),
                'parameters' => array(
                    'participant_ids' => array(
                        'required' => true
                    )
                )
            ),
            'add_category' => array(
                'call_function' => 'addCategory',
                'check_role' => array( 'can_add_category' ),
                'parameters' => array(
                    'category_id' => array(
                        'required' => true
                    ),
                    'assign_to_approver' => array(
                        'required' => false
                    )
                )
            ),
            'add_area' => array(
                'call_function' => 'addArea',
                'check_role' => array( 'can_add_area' ),
                'parameters' => array(
                    'area_id' => array(
                        'required' => true
                    )
                )
            ),
            'add_ente_type' => array(
                'call_function' => 'AddEnteType',
                'check_role' => array( 'can_add_area' ),
                'parameters' => array(
                    'ente_type_id' => array(
                        'required' => true
                    )
                )
            ),
            'set_expiry' => array(
                'call_function' => 'setExpiry',
                'check_role' => array( 'can_set_expiry' ),
                'parameters' => array(
                    'expiry_days' => array(
                        'required' => true
                    )
                )
            ),
            'add_comment' => array(
                'call_function' => 'addComment',
                'check_role' => array( 'can_comment' ),
                'parameters' => array(
                    'text' => array(
                        'required' => true
                    )
                )
            ),
            'add_message' => array(
                'call_function' => 'addMessage',
                'check_role' => array( 'can_send_private_message' ),
                'parameters' => array(
                    'text' => array(
                        'required' => true
                    ),
                    'participant_ids' => array(
                        'required' => true
                    )
                )
            ),
            'add_response' => array(
                'call_function' => 'addResponse',
                'check_role' => array( 'can_respond' ),
                'parameters' => array(
                    'text' => array(
                        'required' => true
                    )
                )
            ),
            'add_attachment' => array(
                'call_function' => 'addAttachment',
                'check_role' => array( 'can_add_attachment' ),
                'parameters' => array(
                    'file' => array(
                        'required' => true
                    )
                )
            ),
            'remove_attachment' => array(
                'call_function' => 'removeAttachment',
                'check_role' => array( 'can_add_attachment' ),
                'parameters' => array(
                    'filename' => array(
                        'required' => true
                    )
                )
            ),
            'edit_comment' => array(
                'call_function' => 'editComment',
                'check_role' => array( 'can_comment' ),
                'parameters' => array(
                    'id_text' => array(
                        'required' => true
                    )
                )
            ),
            'edit_message' => array(
                'call_function' => 'editMessage',
                'check_role' => array( 'can_send_private_message' ),
                'parameters' => array(
                    'id_text' => array(
                        'required' => true
                    )
                )
            ),
            'edit_response' => array(
                'call_function' => 'editResponse',
                'check_role' => array( 'can_respond' ),
                'parameters' => array(
                    'id_text' => array(
                        'required' => true
                    )
                )
            ),
        );
    }

    final public static function instance( SensorUserPostRoles $userPostRoles )
    {
        $className = false;
        if ( eZINI::instance( 'ocsensor.ini' )->hasVariable( 'PHPCLasses', 'ActionHandler' ) )
        {
            $className = eZINI::instance( 'ocsensor.ini' )->variable( 'PHPCLasses', 'ActionHandler' );
        }
        if ( $className && class_exists( $className ) )
        {            
            return new $className( $userPostRoles );
        }                
        return new SensorPostActionHandler( $userPostRoles );
    }

    /**
     * @param $actionName
     * @param $actionParameters
     *
     * @return array
     * @throws Exception
     */
    final public function checkAction( $actionName, $actionParameters )
    {
        if ( array_key_exists( $actionName, $this->actions ) )
        {
            $action = $this->actions[$actionName];
            $arguments = array();

            if ( isset( $action['check_role'] ) )
            {
                foreach( $action['check_role'] as $role )
                {
                    if ( !$this->userPostRoles->attribute( $role ) )
                    {
                        throw new Exception( "Current user does not have '$role' role" );
                    }
                }
            }

            foreach ( $action['parameters'] as $parameterName => $parameterOptions )
            {
                if ( !isset( $actionParameters[$parameterName] ) && $parameterOptions['required'] == true )
                {
                    throw new InvalidArgumentException(
                        "$parameterName parameter is required for action $actionName"
                    );
                }
                else
                {
                    $argument = null;
                    if ( isset( $actionParameters[$parameterName] ) )
                        $argument = $actionParameters[$parameterName];
                    elseif ( isset( $actionParameters['default'] ) )
                        $argument = $actionParameters['default'];
                    $arguments[] = $argument;
                }
            }
            return array( $action['call_function'], $arguments );
        }
        else
        {
            throw new BadFunctionCallException( "$actionName action not available" );
        }
    }

    final public function handleAction( $actionName, $actionParameters )
    {
        list( $method, $arguments ) = $this->checkAction( $actionName, $actionParameters );
        eZDebugSetting::writeNotice( 'sensor', "Call {$method} with arguments " . var_export( $arguments, 1 ), __METHOD__ );
        $reflectionMethod = new ReflectionMethod( $this, $method );
        return $reflectionMethod->invokeArgs( $this, $arguments );
    }

    public function read()
    {
        $this->post->getCollaborationItem()->setLastRead();
        if ( $this->userPostRoles->isApprover()
             && ( $this->post->isWaiting() || $this->post->isReopened() ) )
        {
            $this->post->setStatus( SensorPost::STATUS_READ );
            $this->post->timelineHelper->add( SensorPost::STATUS_READ )->store();
            $this->post->eventHelper->createEvent( 'on_read' );
        }
    }

    public function assign( $participantIds )
    {
        //@todo verificare multi owner
        $isChanged = false;

        $currentApproverIds = $this->post->getParticipants( SensorUserPostRoles::ROLE_APPROVER );
        $currentOwnerIds = $this->post->getParticipants( SensorUserPostRoles::ROLE_OWNER );
        $makeOwnerIds = array_diff( $participantIds, $currentOwnerIds, $currentApproverIds );
        $makeObserverIds = array_diff( $currentOwnerIds, $participantIds );

        $debugArray = array(
            'request' => $participantIds,
            'current_approvers' => $currentApproverIds,
            'current_owners' => $currentOwnerIds,
            'new_owners' => $makeOwnerIds,
            'new_observers' => $makeObserverIds,
        );
        eZDebugSetting::writeNotice( 'sensor', var_export( $debugArray, 1 ), __METHOD__ );

        if ( $makeObserverIds == $currentOwnerIds && empty( $makeOwnerIds  ) )
        {
            return;
        }

        foreach( $makeOwnerIds as $id )
        {
            $this->post->addParticipant( $id, SensorUserPostRoles::ROLE_OWNER );
            $isChanged = true;
        }
        if ( $isChanged )
        {
            foreach( $makeObserverIds as $id )
            {
                $this->post->addParticipant( $id, SensorUserPostRoles::ROLE_OBSERVER );
            }
            $this->post->setStatus( SensorPost::STATUS_ASSIGNED );
            $this->post->timelineHelper->add( SensorPost::STATUS_ASSIGNED, $makeOwnerIds )->store();
            $this->post->eventHelper->createEvent( 'on_assign', array( 'owners' => $makeOwnerIds ) );            
        }
    }

    public function fix()
    {
        //@todo verificare multi owner
        $this->post->addParticipant( $this->userInfo->user()->id(), SensorUserPostRoles::ROLE_OBSERVER );
        if ( !$this->post->hasOwner() )
        {
            $this->post->setStatus( SensorPost::STATUS_FIXED );
        }
        else
        {
            $this->post->touch();
        }
        $this->post->timelineHelper->add( SensorPost::STATUS_FIXED, eZUser::currentUserID() )->store();
        $this->post->eventHelper->createEvent( 'on_fix' );
    }

    public function forceFix()
    {
        //@todo verificare multi owner
        foreach( $this->post->getOwners() as $ownerId )
        {
            $this->post->addParticipant(
                $ownerId,
                SensorUserPostRoles::ROLE_OBSERVER
            );
        }
        if ( !$this->post->hasOwner() )
        {
            $this->post->setStatus( SensorPost::STATUS_FIXED );
        }
        else
        {
            $this->post->touch();
        }
        $this->post->timelineHelper->add( SensorPost::STATUS_FIXED, eZUser::currentUserID() )->store();
        $this->post->eventHelper->createEvent( 'on_force_fix' );
    }

    public function close()
    {
        $this->post->setStatus( SensorPost::STATUS_CLOSED );
        $this->post->timelineHelper->add( SensorPost::STATUS_CLOSED, eZUser::currentUserID() )->store();
        $this->post->eventHelper->createEvent( 'on_close' );
    }

    public function reopen()
    {
        $this->post->setStatus( SensorPost::STATUS_REOPENED );
        $this->post->timelineHelper->add( SensorPost::STATUS_REOPENED, eZUser::currentUserID() )->store();
        $this->post->eventHelper->createEvent( 'on_reopen' );
    }

    public function makePrivate()
    {
        $this->post->objectHelper->makePrivate();
        $this->post->eventHelper->createEvent( 'on_make_private' );
        $this->post->touch();
    }

    public function makePublic()
    {
        $this->post->objectHelper->makePublic();
        $this->post->eventHelper->createEvent( 'on_make_public' );
        $this->post->touch();
    }

    public function moderate( $status )
    {
        $this->post->objectHelper->moderate( $status );
        $this->post->eventHelper->createEvent( 'on_moderate' );
        $this->post->touch();
    }

    public function addObserver( $participantIds )
    {
        $participantIds = (array) $participantIds;
        $isChanged = false;
        $currentApproverIds = $this->post->getParticipants( SensorUserPostRoles::ROLE_APPROVER );
        $currentOwnerIds = $this->post->getParticipants( SensorUserPostRoles::ROLE_OWNER );
        $currentObserverIds = $this->post->getParticipants( SensorUserPostRoles::ROLE_OBSERVER );
        $makeObserverIds = array_intersect( $currentObserverIds, $participantIds );
        $makeObserverIds = array_diff( $participantIds, $currentObserverIds, $currentApproverIds, $currentOwnerIds );
        $debugArray = array(
            'request' => $participantIds,
            'current_approvers' => $currentApproverIds,
            'current_observers' => $currentObserverIds,
            'current_owners' => $currentOwnerIds,
            'new_observers' => $makeObserverIds,
        );
        eZDebugSetting::writeNotice( 'sensor', var_export( $debugArray, 1 ), __METHOD__ );

        foreach( $makeObserverIds as $id )
        {
            $this->post->addParticipant( $id, SensorUserPostRoles::ROLE_OBSERVER );
            $isChanged = true;
        }
        if ( $isChanged )
        {
            $this->post->eventHelper->createEvent( 'on_add_observer', array( 'observers' => $makeObserverIds ) );
            $this->post->touch();
        }
    }

    public function addCategory( array $categoryIdList, $autoAssign = false )
    {
        if ( !empty( $categoryIdList ) )
        {

            if ( $this->post->configParameters['UniqueCategoryCount'] )
            {
                $categoryIdList = array( array_shift( $categoryIdList ) );
            }

            $categoryIdList = ezpEvent::getInstance()->filter( 'sensor/set_categories',  $categoryIdList );
            $categoryString = implode( '-', $categoryIdList );
            $this->post->objectHelper->setContentObjectAttribute( 'category', $categoryString );
            $this->post->eventHelper->createEvent( 'on_add_category', array( 'categories' => $categoryIdList ) );

            if ( $this->post->configParameters['CategoryAutomaticAssign'] )
            {
                $userIds = $this->post->objectHelper->getApproverIdsByCategory();
                $userIds = ezpEvent::getInstance()->filter(
                    'sensor/user_by_categories',
                    $userIds
                );
                if ( !empty( $userIds ) )
                {
                    $this->assign( $userIds );
                }
            }
            $this->post->touch();
        }
    }

    public function addArea( array $areaIdList )
    {
        if ( !empty( $areaIdList ) )
        {
            $categoryIdList = ezpEvent::getInstance()->filter( 'sensor/set_areas',  $areaIdList );
            $areasString = implode( '-', $areaIdList );
            $this->post->objectHelper->setContentObjectAttribute( 'area', $areasString );
            $this->post->eventHelper->createEvent( 'on_add_area', array( 'areas' => $categoryIdList ) );

            if ( $this->post->configParameters['CategoryAreaAssign'] )
            {
                $userIds = $this->post->objectHelper->getApproverIdsByArea();
                $userIds = ezpEvent::getInstance()->filter(
                    'sensor/user_by_areas',
                    $userIds
                );
                if ( !empty( $userIds ) )
                {
                    $this->assign( $userIds );
                }
            }
            $this->post->touch();
        }
    }

    public function addEnteType( $enteTypeID )
    {
        $this->post->objectHelper->setContentObjectAttribute( 'ente_type', $enteTypeID );
        $this->post->touch();
    }

    public function setExpiry( $days )
    {
        $value = intval( $days );
        if ( $value > 0 )
        {
            $this->post->setExpiry( $value );
            $this->post->eventHelper->createEvent( 'on_set_expiry', array( 'expiry' => $value ) );
            $this->post->touch();
        }
    }

    public function addComment( $text )
    {
        if ( $this->post->commentHelper->isValidText( $text ) )
        {
            $this->post->commentHelper->add($text)->store();
            $this->post->eventHelper->createEvent('on_add_comment', array('text' => $text));
            if ($this->post->isClosed() && $this->userPostRoles->isAuthor() && $this->post->configParameters['AuthorCanReopen'])
            {
                $this->post->setStatus(SensorPost::STATUS_REOPENED);
                $this->post->timelineHelper->add(SensorPost::STATUS_REOPENED, eZUser::currentUserID())->store();
                $this->post->eventHelper->createEvent('on_reopen');
            }
            else
            {
                $this->post->touch();
            }
        }
    }

    public function addMessage( $text, $privateReceivers = array() )
    {
        if ( $this->post->messageHelper->isValidText( $text ) )
        {
            $this->post->messageHelper->add($text)->to($privateReceivers)->store();
            $this->post->eventHelper->createEvent('on_add_message');
            $this->post->touch();
        }
    }

    public function addResponse( $text )
    {
        if ( $this->post->responseHelper->isValidText( $text ) )
        {
            $this->post->responseHelper->add($text)->store();
            $this->post->eventHelper->createEvent('on_add_response');
            $this->post->touch();
        }
    }

    public function addAttachment( eZHTTPFile $file )
    {
        if ($attachment = $this->post->addAttachment($file)) {
            $this->post->eventHelper->createEvent('on_add_attachment', array('attachment' => $attachment));
            $this->post->touch();
        }
    }

    public function removeAttachment( $filename )
    {
        if ($this->post->removeAttachment($filename)) {
            $this->post->eventHelper->createEvent('on_remove_attachment', array('attachment' => $filename));
            $this->post->touch();
        }
    }

    public function editComment( $idTextArray )
    {
        foreach( $idTextArray as $id => $text )
        {
            $this->post->commentHelper->edit( $id, $text );
        }
        $this->post->eventHelper->createEvent( 'on_edit_comment' );
        $this->post->touch();
    }

    public function editMessage( $idTextArray )
    {
        foreach( $idTextArray as $id => $text )
        {
            $this->post->messageHelper->edit( $id, $text );
        }
        $this->post->eventHelper->createEvent( 'on_edit_message' );
        $this->post->touch();
    }

    public function editResponse( $idTextArray )
    {
        foreach( $idTextArray as $id => $text )
        {
            $this->post->responseHelper->edit( $id, $text );
        }
        $this->post->eventHelper->createEvent( 'on_edit_response' );
        $this->post->touch();
    }


}