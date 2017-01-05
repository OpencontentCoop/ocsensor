<?php
/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();
$id = $Params['ID'];
$object = eZContentObject::fetch( $id );
if ( $object instanceof eZContentObject )
{
    $module->redirectTo( 'content/edit/' . $object->attribute( 'id' ) . '/f/' . $object->attribute( 'current_language' ) );
    return;
}
else
    return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );