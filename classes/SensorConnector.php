<?php

use Opencontent\Sensor\Api\Action\Action;
use Opencontent\Sensor\Api\Values\Post;
use Opencontent\Sensor\OpenApi\Controller;

class SensorConnector extends Controller
{
    private $eventName;

    private $rawPost;

    private $rawUser;

    private $eventParameters;

    private $currentPostPayload;

    /**
     * @var SensorConnectorConfiguration
     */
    private $configuration;

    public function run(SensorConnectorConfiguration $configuration)
    {
        $this->configuration = $configuration;
        $payload = parent::getPayload();
        $this->eventName = $payload['event'];
        $this->eventParameters = $payload['parameters'];
        $this->rawPost = $payload['post'];
        $this->rawUser = $payload['user'];

        $this->repository->setCurrentUser(
            $this->repository->getUserService()->loadUser($this->configuration->getUserId())
        );

        $result = new ezpRestMvcResult();
        $this->setCurrentPostPayload();

        $response = false;
        if ($this->eventName == 'on_assign') {
            $response = $this->open();
        }elseif ($this->eventName == 'on_fix' || $this->eventName == 'on_close' || $this->eventName == 'change_owner') {
            $response = $this->close();
        }
        $result->variables = ['action' => $response];

        return $result;
    }

    private function getLocalPost()
    {
        $remoteId = $this->configuration->generateRemoteId($this->rawPost['id']);
        $exists = eZContentObject::fetchByRemoteID($remoteId);
        if ($exists instanceof eZContentObject) {
            $query = 'id = ' . $exists->attribute('id') . ' limit 1';
            $posts = $this->repository->getSearchService()->searchPosts($query, [], []);
            if ($posts->totalCount > 0) {
                return $posts->searchHits[0];
            }
        }

        return false;
    }

    private function open()
    {
        $remoteId = $this->configuration->generateRemoteId($this->rawPost['id']);
        $localPost = $this->getLocalPost();
        if ($localPost instanceof Post) {
            if ($localPost->workflowStatus->is(Post\WorkflowStatus::CLOSED)){
                $action = new Action();
                $action->identifier = 'reopen';
                $this->repository->getActionService()->loadActionDefinitionByIdentifier($action->identifier)->run(
                    $this->repository,
                    $action,
                    $localPost,
                    $this->repository->getCurrentUser()
                );
                return 'Reopen ' . $localPost->id;
            }
        } else {
            $user = $this->configuration->getUser();
            if ($user instanceof eZUser) {
                eZUser::setCurrentlyLoggedInUser($user, $user->id());
            }
            $localPost = $this->createPost();
            $object = eZContentObject::fetch((int)$localPost->variables['id']);
            if ($object instanceof eZContentObject) {
                $object->setAttribute('remote_id', $remoteId);
                $object->store();
            }
            if ($user instanceof eZUser) {
                $user->logoutCurrent();
            }
            return 'Create ' . $localPost->id;
        }

        return false;
    }

    private function close()
    {
        $localPost = $this->getLocalPost();
        if ($localPost instanceof Post && !$localPost->workflowStatus->is(Post\WorkflowStatus::CLOSED)) {
            $action = new Action();
            $action->identifier = 'close';
            $this->repository->getActionService()->loadActionDefinitionByIdentifier($action->identifier)->run(
                $this->repository,
                $action,
                $localPost,
                $this->repository->getCurrentUser()
            );
            return 'Close ' . $localPost->id;
        }

        return false;
    }

    private function setCurrentPostPayload()
    {
        $images = [];
        if (isset($this->rawPost['images'])) {
            foreach ($this->rawPost['images'] as $image) {
                $imageFileContents = file_get_contents($image);
                if ($imageFileContents) {
                    $images[] = [
                        'filename' => basename($image),
                        'file' => base64_encode($imageFileContents),
                    ];
                }
            }
        }
        $address = $this->rawPost['address'];
        if (empty($this->rawPost['address']['latitude'])){
            $address = null;
        }
        $this->currentPostPayload = [
            'address' => $address,
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