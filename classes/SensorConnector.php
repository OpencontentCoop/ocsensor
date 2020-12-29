<?php

use Opencontent\Sensor\Api\Exception\InvalidArgumentException;
use Opencontent\Sensor\OpenApi\Controller;

class SensorConnector extends Controller
{
    private $currentEvent;

    private $currentPost;

    private $currentUser;

    private $currentParameters;

    public function run(SensorConnectorConfiguration $configuration)
    {
        $result = new ezpRestMvcResult();
        $this->setCurrentPayload();
        if (!in_array($this->currentEvent, $this->getValidEvents())){
            throw new InvalidArgumentException();
        }
        $this->setCurrentPost();
        if ($this->currentEvent == 'on_assign'){
            $remoteId = $configuration->generateRemoteId($this->currentPost['id']);
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

    private function getValidEvents()
    {
        return [
            'on_assign',
        ];
    }

    private function setCurrentPost()
    {
        unset($this->currentPost['area']);
        unset($this->currentPost['areas']);
        unset($this->currentPost['type']);
        unset($this->currentPost['image']);
        $this->currentPost['is_private'] = true;
        if (isset($this->currentPost['images'])) {
            $images = [];
            foreach ($this->currentPost['images'] as $image){
                $images[] = [
                    'filename' => basename($image),
                    'file' => base64_encode(file_get_contents($image)),
                ];
            }
            $this->currentPost['images'] = $images;
        }
    }

    private function setCurrentPayload()
    {
        $payload = parent::getPayload();
        $this->currentEvent = $payload['event'];
        $this->currentPost = $payload['post'];
        $this->currentUser = $payload['post'];
        $this->currentParameters = $payload['parameters'];
    }

    protected function getPayload()
    {
        return $this->currentPost;
    }


}