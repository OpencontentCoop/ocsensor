<?php

use Opencontent\Sensor\Api\Action\Action;
use Opencontent\Sensor\Api\Values\Post;
use Opencontent\Sensor\OpenApi\Controller;

class SensorConnector extends Controller
{
    const EVENT_OPEN = 'send_open';

    const EVENT_CLOSE = 'send_close';

    const EVENT_RECEIVE_CLOSE = 'receive_close';

    private $eventName;

    private $rawPost;

    private $currentPostPayload;

    /**
     * @var SensorConnectorConfiguration
     */
    private $configuration;

    private $user;

    public function run(SensorConnectorConfiguration $configuration)
    {
        $this->configuration = $configuration;
        $payload = parent::getPayload();
        $this->eventName = $payload['event'];
        $this->rawPost = $payload['post'];

        $this->user = eZUser::fetchByName($this->configuration->getUserName());
        if ($this->user instanceof eZUser) {
            $this->repository->setCurrentUser(
                $this->repository->getUserService()->loadUser($this->user->id())
            );
        }

        $result = new ezpRestMvcResult();
        $this->setCurrentPostPayload();

        $response = false;
        if ($this->eventName == self::EVENT_OPEN) {
            $response = $this->open();
        } elseif ($this->eventName == self::EVENT_CLOSE) {
            $response = $this->close();
        } elseif ($this->eventName == self::EVENT_RECEIVE_CLOSE) {
            $response = $this->fix();
        }

        $result->variables = ['message' => $response];

        return $result;
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
        if (empty($this->rawPost['address']['latitude'])) {
            $address = null;
        }
        $this->currentPostPayload = [
            'address' => $address,
            'type' => $this->rawPost['type'],
            'subject' => $this->rawPost['subject'],
            'description' => $this->rawPost['description'],
            'is_private' => true,
            'images' => $images,
            'meta' => $this->rawPost['address_meta_info'],
        ];
    }

    private function open()
    {
        $localPost = $this->getLocalPost();
        if ($localPost instanceof Post) {
            if ($localPost->workflowStatus->is(Post\WorkflowStatus::CLOSED)) {
                $action = new Action();
                $action->identifier = 'reopen';
                $this->repository->getActionService()->loadActionDefinitionByIdentifier($action->identifier)->run(
                    $this->repository,
                    $action,
                    $localPost,
                    $this->repository->getCurrentUser()
                );
                return 'Reopen post ' . $localPost->id;
            }
        } else {
            if ($this->user instanceof eZUser) {
                eZUser::setCurrentlyLoggedInUser($this->user, $this->user->id());
            }
            $result = $this->createPost();
            $object = eZContentObject::fetch((int)$result->variables['id']);
            if ($object instanceof eZContentObject) {
                $remoteId = $this->configuration->generateRemoteId($this->rawPost['id']);
                $object->setAttribute('remote_id', $remoteId);
                $object->store();
            }
            if ($this->user instanceof eZUser) {
                $this->user->logoutCurrent();
            }
            return 'Create post ' . $result->variables['id'];
        }

        return false;
    }

    private function getLocalPost()
    {
        $id = false;
        if ($this->eventName == self::EVENT_RECEIVE_CLOSE) {
            $id = $this->rawPost['id'];
        } else {
            $remoteId = $this->configuration->generateRemoteId($this->rawPost['id']);
            $exists = eZContentObject::fetchByRemoteID($remoteId);
            if ($exists instanceof eZContentObject) {
                $id = $exists->attribute('id');
            }
        }
        if ($id) {
            $query = 'id = ' . $id . ' limit 1';
            $posts = $this->repository->getSearchService()->searchPosts($query, [], []);
            if ($posts->totalCount > 0) {
                return $posts->searchHits[0];
            }
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
            return 'Close post ' . $localPost->id;
        }

        return false;
    }

    private function fix()
    {
        $localPost = $this->getLocalPost();
        if ($localPost instanceof Post && !$localPost->workflowStatus->is(Post\WorkflowStatus::CLOSED)) {
            $response = $this->rawPost['response'];
            if (!empty($response)) {
                $action = new Action();
                $action->identifier = 'send_private_message';
                $action->setParameter('text', $response['text']);
                $this->repository->getActionService()->loadActionDefinitionByIdentifier($action->identifier)->run(
                    $this->repository,
                    $action,
                    $localPost,
                    $this->repository->getCurrentUser()
                );
            }

            $action = new Action();
            $action->identifier = 'fix';
            $this->repository->getActionService()->loadActionDefinitionByIdentifier($action->identifier)->run(
                $this->repository,
                $action,
                $localPost,
                $this->repository->getCurrentUser()
            );

            return 'Fix post ' . $localPost->id;
        }
        return false;
    }

    protected function getPayload()
    {
        return $this->currentPostPayload;
    }


}