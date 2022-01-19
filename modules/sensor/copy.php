<?php
/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();
$id = $Params['Id'];
$node = OpenPaSensorRepository::instance()->getPostRootNode();
$class = OpenPaSensorRepository::instance()->getPostContentClass();
$object = eZContentObject::fetch((int)$id);

if ($node instanceof eZContentObjectTreeNode && $class instanceof eZContentClass && $object instanceof eZContentObject) {

    try {

        $isValid = false;
        if ($object instanceof eZContentObject && $object->attribute('class_identifier') == $class->attribute('identifier') && $object->canRead()){
            if (eZUser::currentUserID() == $object->attribute('owner_id')){
                $isValid = true;
            }else{
                $post = OpenPaSensorRepository::instance()->getPostService()->loadPost($object->attribute('id'));
                $isValid = eZUser::currentUserID() == $post->reporter->id || OpenPaSensorRepository::instance()->getCurrentUser()->behalfOfMode == true;
            }
        }

        if (!$isValid){
            throw new Exception("Current user is not creator neither reporter neither approver");
        }

        $copy = OpenPAObjectTools::copyObject($object);

        $dataMap = $copy->dataMap();
        if (isset($dataMap['subject'])) {
            $dataMap['subject']->fromString($dataMap['subject']->toString() . ' (copia)');
            $dataMap['subject']->store();
        }

        if (isset($dataMap['reporter'])){
            $dataMap['reporter']->fromString(eZUser::currentUserID());
            $dataMap['reporter']->store();
        }

        if (isset($dataMap['on_behalf_of'])){
            $dataMap['on_behalf_of']->fromString($object->attribute('owner_id'));
            $dataMap['on_behalf_of']->store();
        }

        $object->addContentObjectRelation($copy->attribute('id'));

        eZSys::addAccessPath(array('layout', 'set', 'sensor_add'), 'layout', false);
        $module->redirectTo('content/edit/' . $copy->attribute('id') . '/' . $copy->attribute('current_version'));

        return;
    } catch (InvalidArgumentException $e) {
        return $module->handleError(eZError::KERNEL_NOT_AVAILABLE, 'kernel');

    } catch (Exception $e) {

        return $module->handleError(eZError::KERNEL_ACCESS_DENIED, 'kernel');
    }
} else {

    return $module->handleError(eZError::KERNEL_NOT_AVAILABLE, 'kernel');
}
