<?php

$module = $Params['Module'];
$tpl = eZTemplate::factory();
$ini = eZINI::instance();
$http = eZHTTPTool::instance();
$test = $Params['Type'];
$objectId = $Params['Id'];
$participantRole = $Params['Param'];
$eventIdentifier = $Params['Param2'];

echo '<pre>';

try
{
    $repository = OpenPaSensorRepository::instance();

    if ( $test == 'test' )
    {
        //$repository->getPostService()->clearCache( $objectId );
        $post = $repository->getPostService()->loadPost( $objectId );
    }
    else
        $post = $repository->getPostService()->loadPostByInternalId( $objectId );

//    $user = $repository->getUserService()->loadUser($post->approvers->first()->id);
//    $repository->getUserService()->setUserPostAware( $user, $post );
//    $repository->setCurrentUser($user);
//
//    $action = new \OpenContent\Sensor\Api\Action\Action();
//    $action->identifier = 'fix';
////    $action->setParameter( 'participant_ids', array(1924,1922) );
//    $repository->getActionService()->runAction( $action, $post );
////
//    $post = $repository->getPostService()->loadPost( $objectId );
//    print_r( $repository->getCurrentUser() );
    print_r( $post );

    $action = new \OpenContent\Sensor\Api\Action\Action();
    $action->identifier = 'read';
    $repository->getActionService()->runAction( $action, $post );

}
catch( Exception $e )
{
    print_r( $e->getMessage() );
    echo '<hr>';
    print_r( $e->getTrace() );
}


eZDisplayDebug();
eZExecution::cleanExit();

$templateResult = false;



$siteUrl = eZINI::instance()->variable( 'SiteSettings', 'SiteURL' );
$parts = explode( '/', $siteUrl );
if ( count( $parts ) >= 2 )
{
    $suffix = array_shift( $parts );
    $siteUrl = implode( '/', $parts );
}
echo '<pre>' . rtrim( $siteUrl, '/' ) . '</pre>';

if ( $test == 'registration' )
{
    $user = eZUser::fetch( 14 );
    if ( $user === null )
        return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
    
    $tpl->setVariable( 'user', $user );
    $templateResult = $tpl->fetch( 'design:social_user/mail/registrationinfo.tpl' );
}
elseif ( $test == 'post' )
{
    $helper = SensorHelper::instanceFromContentObjectId( $objectId );
    $item = $helper->currentSensorPost->getCollaborationItem();
    //$item->createNotificationEvent();    
    $object = eZContentObject::fetch( $item->attribute( "data_int1" ) );
    $node = $object->attribute( 'main_node' );
    if ( !$object instanceof eZContentObject )
    {
        throw new Exception( 'object not found' );
    }
    /** @var SensorCollaborationHandler $itemHandler */
    $itemHandler = $item->attribute( 'handler' );

    $tpl->setVariable( 'event_identifier', $eventIdentifier );
    $tpl->setVariable( 'event_details', array(
        'observers' =>  array( 14 ),
        'owners' =>  array( 14 ),
        'categories'  =>  array( 57 ),
        'areas'  =>  array( 57 ),
        'expiry'  =>  15,
    ));
    $tpl->setVariable( 'event_creator', 14 );
    $tpl->setVariable( 'event_timestamp', time() );
    
    $templateName = SensorNotificationHelper::notificationMailTemplate( $participantRole );
    $templatePath = 'design:sensor/mail/' . $eventIdentifier . '/' . $templateName;

    $tpl->setVariable( 'collaboration_item', $item );
    $tpl->setVariable( 'collaboration_participant_role', $participantRole );
    $tpl->setVariable( 'collaboration_item_status', $item->attribute( SensorPost::COLLABORATION_FIELD_STATUS ) );
    $tpl->setVariable( 'sensor_post', $helper );
    $tpl->setVariable( 'object', $object );
    $tpl->setVariable( 'node', $node );

    $result = $tpl->fetch( $templatePath );

    $body = $tpl->variable( 'body' );
    $subject = $tpl->variable( 'subject' );

    $tpl->setVariable( 'title', $subject );
    $tpl->setVariable( 'content', $body );

    $templateResult = "<pre>{$subject}</pre>";
    $templateResult .= $tpl->fetch( 'design:mail/sensor_mail_pagelayout.tpl' );

}

echo $templateResult;


eZDisplayDebug();
eZExecution::cleanExit();
