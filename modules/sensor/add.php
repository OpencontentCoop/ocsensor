<?php
/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();

$node = SensorHelper::postContainerNode();
$class = SensorHelper::postContentClass();

if ( $node instanceof eZContentObjectTreeNode && $class instanceof eZContentClass )
{
    $languageCode = eZINI::instance()->variable( 'RegionalSettings', 'Locale' );
    $object = eZContentObject::createWithNodeAssignment( $node,
        $class->attribute( 'id' ),
        $languageCode,
        false );
    if ( $object )
    {
        $module->redirectTo( 'content/edit/' . $object->attribute( 'id' ) . '/' . $object->attribute( 'current_version' ) );
        return;
    }
    else
        return $module->handleError( eZError::KERNEL_ACCESS_DENIED, 'kernel' );
}
else
    return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );