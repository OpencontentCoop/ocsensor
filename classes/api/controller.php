<?php

class SensorApiController extends ezpRestMvcController
{

    /** @todo */
    public function doHelp()
    {
        return new ezpRestMvcResult();
    }

    public function doLoadPost()
    {
        $apiPost = SensorApiPost::fromId( $this->Id );
        $result = new ezpRestMvcResult();
        $result->variables['post'] = $apiPost->toHash();
        $result->variables['links'] = SensorApiModel::getLinksByPost( $apiPost, $this->request );
        return $result;
    }

    public function doCreatePost()
    {
        $result = new ezpRestMvcResult();
        $result->variables['todo'] = 'coming soon...';
        return $result;
    }

    public function doUpdatePost()
    {
        $result = new ezpRestMvcResult();
        $result->variables['todo'] = 'coming soon...';
        return $result;
    }

    public function doViewDetail()
    {
        $apiPost = SensorApiPost::fromId( $this->Id );
        if ( $apiPost->hasAttribute( $this->Detail ) )
        {
            $result = new ezpRestMvcResult();
            $result->variables[$this->Detail] = $apiPost->attribute( $this->Detail );
            return $result;
        }
        elseif ( SensorApiModel::hasDetail( $this->Detail ) )
        {
            $result = new ezpRestMvcResult();
            $result->variables['todo'] = 'coming soon...';
            return $result;   
        }        
        throw new Exception( "Not found" );
    }

    // api che rispondono alla maniera del vecchio SensorCivico, utilizzate dal Comune di Trento
    public function doCompatUpdatePost()
    {
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

        $post = SensorHelper::instanceFromContentObjectId( $id );

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
        return $result;
    }

}