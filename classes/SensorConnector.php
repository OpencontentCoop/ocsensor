<?php

use Opencontent\Sensor\Api\Exception\InvalidArgumentException;
use Opencontent\Sensor\OpenApi\Controller;

class SensorConnector extends Controller
{
    private $eventName;

    private $rawPost;

    private $rawUser;

    private $eventParameters;

    private $currentPostPayload;

    public function run(SensorConnectorConfiguration $configuration)
    {
        $payload = parent::getPayload();
        $this->eventName = $payload['event'];
        $this->eventParameters = $payload['parameters'];
        $this->rawPost = $payload['post'];
        $this->rawUser = $payload['user'];

        $result = new ezpRestMvcResult();
        $this->setCurrentPostPayload();

        if ($this->eventName == 'on_assign'){
            $remoteId = $configuration->generateRemoteId($this->rawPost['id']);
            $exists = eZContentObject::fetchByRemoteID($remoteId);
            if ($exists instanceof eZContentObject){
                $this->restController->postId = $exists->attribute('id');
                $result = $this->updatePostById();
            }else{
                $result = $this->createPost();
                $object = eZContentObject::fetch((int)$result->variables['id']);
                if ($object instanceof eZContentObject) {
                    $object->setAttribute('remote_id', $remoteId);
                    $object->store();
                }
            }
        }
        return $result;
    }

    private function setCurrentPostPayload()
    {
        $images = [];
        if (isset($this->rawPost['images'])) {
            foreach ($this->rawPost['images'] as $image){
                $imageFileContents = file_get_contents($image);
                if ($imageFileContents) {
                    $images[] = [
                        'filename' => basename($image),
                        'file' => base64_encode($imageFileContents),
                    ];
                }
            }
        }
        $this->currentPostPayload = [
            'address' => $this->rawPost['address'],
            'type' => $this->rawPost['type'],
            'subject' => $this->rawPost['subject'],
            'description' => $this->rawPost['description'],
            'is_private' => true,
            'images' => $images,
        ];
    }

    protected function getPayload()
    {
        return $this->currentPostPayload;
    }


}