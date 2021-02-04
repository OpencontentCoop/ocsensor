<?php

use League\Event\AbstractListener;
use League\Event\EventInterface;
use Opencontent\Sensor\Api\Values\Event as SensorEvent;
use Opencontent\Sensor\Legacy\Repository;

class SensorFlashMessageListener extends AbstractListener
{
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(EventInterface $event, $param = null)
    {
        if ($param instanceof SensorEvent){
            $message = false;
            switch ($param->identifier){
                case 'on_add_approver':
                    $approverNameList = [];
                    /** @var eZContentObject[] $objects */
                    $objects = eZContentObject::fetchIDArray($param->parameters['approvers']);
                    foreach ($objects as $object) {
                        $approverNameList[] = $object->attribute('name');
                    }
                    if (count($approverNameList) > 0){
                        if (count($approverNameList) == 1){
                            $message = 'Aggiunto ';
                        }else{
                            $message = 'Aggiunti ';
                        }
                        $message .= implode(',', $approverNameList) . ' al ruolo riferimento per il cittadino';
                    }
                    break;

                case 'on_assign':
                    $ownerNameList = [];
                    /** @var eZContentObject[] $objects */
                    $objects = eZContentObject::fetchIDArray($param->parameters['owners']);
                    foreach ($objects as $object) {
                        $ownerNameList[] = $object->attribute('name');
                    }
                    if (count($ownerNameList) > 0){
                        if (count($ownerNameList) == 1){
                            $message = 'Aggiunto ';
                        }else{
                            $message = 'Aggiunti ';
                        }
                        $message .= implode(',', $ownerNameList) . ' al ruolo incaricato';
                    }
                    break;

                case 'on_add_observer':
                    $observerNameList = [];
                    /** @var eZContentObject[] $objects */
                    $objects = eZContentObject::fetchIDArray($param->parameters['observers']);
                    foreach ($objects as $object) {
                        $observerNameList[] = $object->attribute('name');
                    }
                    if (count($observerNameList) > 0){
                        if (count($observerNameList) == 1){
                            $message = 'Aggiunto ';
                        }else{
                            $message = 'Aggiunti ';
                        }
                        $message .= implode(',', $observerNameList) . ' al ruolo osservatore';
                    }
                    break;
            }
            if ($message && $this->repository->getCurrentUser()->type == 'sensor_operator') {
                $this->repository->getLogger()->info("Add flash alert for '{$param->identifier}'");
                SensorUserInfo::addFlashAlert($message, 'info');
            }
        }
    }
}