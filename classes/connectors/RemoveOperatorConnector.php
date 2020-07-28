<?php

use Opencontent\Ocopendata\Forms\Connectors\AbstractBaseConnector;
use Opencontent\Sensor\Api\Values\User;

class RemoveOperatorConnector extends AbstractBaseConnector
{
    /**
     * @var eZContentObject
     */
    private $object;

    public function runService($serviceIdentifier)
    {
        if ($this->getHelper()->hasParameter('object')) {
            $this->object = eZContentObject::fetch((int)$this->getHelper()->getParameter('object'));
        }

        if (!$this->object instanceof eZContentObject) {
            throw new Exception("Object not found", 1);

        }

        if (!$this->object->canRemove()) {
            throw new Exception("Current user can not remove object #" . $this->getHelper()->getParameter('object'), 1);

        }

        if ($serviceIdentifier == 'data') {
            return $this->getData();

        } elseif ($serviceIdentifier == 'schema') {
            return $this->getSchema();

        } elseif ($serviceIdentifier == 'options') {
            return $this->getOptions();

        } elseif ($serviceIdentifier == 'view') {
            return $this->getView();

        } elseif ($serviceIdentifier == 'action') {
            return $this->submit();

        } elseif ($serviceIdentifier == '') {
            return $this->getAll();

        }

        throw new \Exception("Connector service $serviceIdentifier not handled");
    }

    protected function getData()
    {
        return null;
    }

    protected function getSchema()
    {
        return array(
            "title" => "Sei sicuro di voler disattivare l'operatore " . $this->object->attribute('name') . '?',
            "type" => "object"
        );
    }

    protected function getOptions()
    {
        return array(
            "form" => array(
                "attributes" => array(
                    "action" => $this->getHelper()->getServiceUrl('action', $this->getHelper()->getParameters()),
                    "method" => "post"
                ),
                "buttons" => array(
                    "submit" => array()
                ),
            )
        );
    }

    protected function getView()
    {
        return array(
            "parent" => "bootstrap-edit",
            "locale" => "it_IT"
        );
    }

    protected function submit()
    {
        $repository = OpenPaSensorRepository::instance();
        $moveToTrash = false;
        $deleteIDArray = array();
        $hasMemberNode = false;
        foreach ($this->object->assignedNodes() as $node) {
            if ($node->attribute('parent_node_id') == $repository->getUserRootNode()->attribute('node_id')){
                $hasMemberNode = true;
            }
            if ($node->attribute('parent_node_id') == $repository->getOperatorsRootNode()->attribute('node_id')){
                $deleteIDArray[] = $node->attribute('node_id');
            }
        }
        if (!$hasMemberNode){
            eZContentOperationCollection::addAssignment(
                $this->object->attribute('main_node_id'),
                $this->object->attribute('id'),
                [$repository->getUserRootNode()->attribute('node_id')]
            );
        }
        if (!empty($deleteIDArray)) {
            if (eZOperationHandler::operationIsAvailable('content_delete')) {
                eZOperationHandler::execute('content',
                    'delete',
                    array(
                        'node_id_list' => $deleteIDArray,
                        'move_to_trash' => $moveToTrash
                    ),
                    null, true);
            } else {
                eZContentOperationCollection::deleteObject($deleteIDArray, $moveToTrash);
            }

            $user = $repository->getUserService()->loadUser($this->object->attribute('id'));
            if ($user instanceof User) {
                eZUserOperationCollection::setSettings($this->object->attribute('id'), false, 0);
                eZUser::purgeUserCacheByUserId($this->object->attribute('id'));
                $allNotifications = $repository->getNotificationService()->getNotificationTypes();
                foreach ($allNotifications as $notification) {
                    $repository->getNotificationService()->removeUserToNotification($user, $notification);
                }
            }
        }

        return true;
    }

    protected function upload()
    {
        throw new Exception("Method not allowed", 1);

    }

}
