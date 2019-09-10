<?php
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$ini = eZINI::instance();
$http = eZHTTPTool::instance();
$templateResult = false;

$test = $Params['Type'];
$objectId = $Params['Id'];
$participantRole = $Params['Param'];
$eventIdentifier = $Params['Param2'];

$siteUrl = rtrim(eZINI::instance()->variable( 'SiteSettings', 'SiteURL' ), '/');
$parts = explode( '/', $siteUrl );
if ( count( $parts ) >= 2 )
{
    $suffix = array_shift( $parts );
    $siteUrl = implode( '/', $parts );
}

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
    $repository = OpenPaSensorRepository::instance();
    $post = $repository->getPostService()->loadPost($objectId);
    $event = new \Opencontent\Sensor\Api\Values\Event();
    $event->identifier = $eventIdentifier;
    $event->post = $post;
    $event->user = $repository->getCurrentUser();
    $event->parameters = array(
        'observers' =>  array( 14 ),
        'owners' =>  array( 14 ),
        'categories'  =>  array( 57 ),
        'areas'  =>  array( 57 ),
        'expiry'  =>  15,
    );
    $notificationType = $repository->getNotificationService()->getNotificationByIdentifier($event->identifier);
    $mailListener = new \Opencontent\Sensor\Legacy\Listeners\MailNotificationListener($repository);
    $data = $mailListener->buildMailDataToRole($event, $notificationType, $participantRole);

    $templateResult = "<pre>{$data['subject']}</pre>";
    $templateResult .= $data['body'];

}

echo $templateResult;


eZDisplayDebug();
eZExecution::cleanExit();
