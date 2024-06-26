<?php


class SensorApiCompatController
{
    // api che rispondono alla maniera del vecchio SensorCivico, utilizzate dal Comune di Trento
    public function doCompatUpdatePost()
    {
        $currentUserHasAccess = eZUser::currentUser()->hasAccessTo( 'sensor', 'ws_user' );
        if ( $currentUserHasAccess['accessWord'] == 'no' )
        {
            throw new Exception( "Current user does not have access to policy sensor/ws_user" );
        }
        $urpUser = eZUser::fetchByName( 'urp' ); //@todo calcolare l'utente corretto
        $urpSensorUser = SensorUserInfo::instance( $urpUser );

        $postData = $this->request->post;
        eZLog::write( var_export( $postData, 1 ), 'sensor_api.trento.log' );
        if ( !isset( $postData['data']['Marker'] ) )
        {
            throw new Exception( "Data not found" );
        }
        $data = $postData['data']['Marker'];
        $id = $data['id'];
        $administrationStatus = isset( $data['administration-status'] ) ? intval( $data['administration-status'] ) : 0;
        $comment = isset( $data['comment'] ) ? $data['comment'] : '';
        $public = isset( $data['public'] ) ? intval( $data['public'] ) == 1 : false;
        $notify = isset( $data['notify'] ) ? $data['notify'] : false; //yet not handled

        $post = SensorHelper::instanceFromContentObjectId( $id, $urpSensorUser );

        $actions = array();

        if ( !empty( $comment ) )
        {
            if ( $public )
            {
                if ( $administrationStatus == 6 )
                    $actions['add_response'] = array( 'text' => $comment );
                else
                    $actions['add_comment'] = array( 'text' => $comment );
            }
            else
            {
                $actions['add_message'] = array(
                    'text' => $comment,
                    'participant_ids' => array( $post->attribute( 'author_id' ), eZUser::currentUserID() )
                );
            }
        }

        switch( $administrationStatus )
        {
            case 2:
                $actions['moderate'] = array( 'status' => 'accepted' );
                break;
            case 4:
                $actions['ws_read'] = array();
                $actions['read'] = array();
                break;
            case 6:
                $actions['moderate'] = array( 'status' => 'accepted' );
                $actions['close'] = array();
                break;
            case 7:
                $actions['close'] = array();
                $actions['make_private'] = array();
                break;
        }

        // dry run to check errors
        foreach( $actions as $action => $parameters )
        {
            $post->currentSensorUserRoles->actionHandler->checkAction(
                $action,
                $parameters
            );
        }

        foreach( $actions as $action => $parameters )
        {
            $post->currentSensorUserRoles->actionHandler->handleAction(
                $action,
                $parameters
            );
        }

        $result = new ezpRestMvcResult();
        if ( count( $actions ) > 0 )
        {
            $result->variables = array(
                'data' => array(
                    'result' => 'Successfully updated post ' . $id,
                    'actions' => $actions
                )
            );
        }
        else
        {
            $result->variables = array(
                'data' => array(
                    'result' => 'No action required for post ' . $id,
                )
            );
        }

        if ( class_exists('Opencontent\Sensor\Legacy\CachedPostService') )
            Opencontent\Sensor\Legacy\CachedPostService::clearCache( $post->attribute( 'id' ) );

        $nodeList = eZContentCacheManager::nodeList($post->attribute( 'id' ), true);
        SensorModuleFunctions::onClearObjectCache($nodeList);

        return $result;
    }
}