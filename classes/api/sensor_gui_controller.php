<?php

use Opencontent\Sensor\Api\Exception\BaseException;
use Opencontent\Sensor\Api\Exception\InvalidArgumentException;
use Opencontent\Sensor\Api\Values\PostCreateStruct;
use Opencontent\Sensor\Api\Values\PostUpdateStruct;

class SensorGuiApiController extends ezpRestMvcController
{
    /**
     * @var ezpRestRequest
     */
    protected $request;

    /**
     * @var \Opencontent\Sensor\Legacy\Repository
     */
    protected $repository;

    /**
     * @var \Opencontent\Sensor\OpenApi
     */
    protected $openApiTools;

    public function __construct($action, ezcMvcRequest $request)
    {
        parent::__construct($action, $request);
        $this->repository = OpenPaSensorRepository::instance();
        $this->openApiTools = new \Opencontent\Sensor\OpenApi(
            $this->repository,
            $this->getHostURI(),
            $this->getBaseUri()
        );
    }

    private function getHostURI()
    {
        $hostUri = $this->request->getHostURI();
        if (eZSys::isSSLNow()) {
            $hostUri = str_replace('http:', 'https:', $hostUri);
        }

        return $hostUri;
    }

    public function doSettings()
    {
        try {
            if ($this->repository->getCurrentUser()->id == eZUser::anonymousId()) {
                throw new \Opencontent\Sensor\Api\Exception\UnauthorizedException();
            }
            $result = new ezpRestMvcResult();
            $settings = [];
            foreach ($this->repository->getSensorSettings()->jsonSerialize() as $key => $value) {
                $settings[] = ['key' => $key, 'value' => $value];
            }
            $result->variables = $settings;
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doLoadPosts()
    {
        try {
            $limit = isset($this->request->get['limit']) ? (int)$this->request->get['limit'] : $this->request->variables['limit'];
            $cursor = isset($this->request->get['cursor']) ? rawurldecode($this->request->get['cursor']) : $this->request->variables['cursor'];
            $data = $this->repository->getPostService()->loadPosts(null, $limit, $cursor);

            $result = new ezpRestMvcResult();
            $resultData = [
                'self' => $this->getBaseUri() . "/posts?limit={$limit}&cursor=" . urlencode($data['current']),
                'items' => $this->openApiTools->replacePlaceholders($data['items']),
                'next' => null,
            ];
            if ($data['next']) {
                $resultData['next'] = $this->getBaseUri() . "/posts?limit={$limit}&cursor=" . rawurlencode($data['next']);
                header("x-next: " . $resultData['next']);
            }
            $result->variables = $resultData;
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doLoadPostById()
    {
        if ($this->Id === 'search') {
            return $this->doPostSearch();
        }

        try {
            $apiPost = $this->openApiTools->replacePlaceholders(
                $this->repository->getSearchService()->searchPost($this->Id)->jsonSerialize()
            );
            $result = new ezpRestMvcResult();
            $result->variables = $apiPost;
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doPostSearch()
    {
        try {
            $query = isset($this->request->get['query']) ? trim($this->request->get['query']) : trim($this->request->get['q']);
            $parameters = [
                'executionTimes' => isset($this->request->get['executionTimes']),
                'readingStatuses' => isset($this->request->get['readingStatuses']),
                'capabilities' => isset($this->request->get['capabilities']),
                'currentUserInParticipants' => isset($this->request->get['currentUserInParticipants']),
                'format' => isset($this->request->get['format']) ? $this->request->get['format'] : 'json',
            ];
            $result = new ezpRestMvcResult();
            $result->variables = $this->openApiTools->replacePlaceholders(
                $this->repository->getSearchService()->searchPosts($query, $parameters)
            );

        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doLoadUserById()
    {
        try {
            if ($this->UserId == 'current') {
                $user = $this->repository->getCurrentUser();
            } else {
                $user = $this->repository->getUserService()->loadUser($this->UserId);
            }
            $apiUser = $this->openApiTools->replacePlaceholders($user->jsonSerialize());
            $result = new ezpRestMvcResult();
            $result->variables = $apiUser;
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doLoadUserPostCapabilities()
    {
        try {
            $result = new ezpRestMvcResult();
            $post = $this->repository->getPostService()->loadPost($this->Id);
            if ($this->UserId == 'current') {
                $result->variables = $this->repository->getPermissionService()->loadCurrentUserPostPermissionCollection($post)->jsonSerialize();
            } else {
                $user = $this->repository->getUserService()->loadUser($this->UserId);
                $result->variables = $this->repository->getPermissionService()->loadUserPostPermissionCollection($user, $post)->jsonSerialize();
            }
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doCreatePost()
    {
        try {
            $result = new ezpRestMvcResult();
            $createStruct = PostCreateStruct::fromArray($this->getPayload());
            $result->variables = $this->repository->getPostService()->createPost($createStruct)->jsonSerialize();
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doUpdatePost()
    {
        try {
            $post = $this->repository->getPostService()->loadPost($this->Id);
            $updateStruct = PostUpdateStruct::fromArray($this->getPayload());
            $updateStruct->setPost($post);
            $result = new ezpRestMvcResult();
            $result->variables = $this->repository->getPostService()->updatePost($updateStruct)->jsonSerialize();
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doDeletePost()
    {
        try {
            $post = $this->repository->getPostService()->loadPost($this->Id);
            $this->repository->getPostService()->deletePost($post);
            $result = new ezpRestMvcResult();
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doLoadOperators()
    {
        try {
            $query = isset($this->request->get['query']) ? trim($this->request->get['query']) : null;
            $limit = isset($this->request->get['limit']) ? (int)$this->request->get['limit'] : $this->request->variables['limit'];
            $cursor = isset($this->request->get['cursor']) ? rawurldecode($this->request->get['cursor']) : $this->request->variables['cursor'];
            $data = $this->repository->getOperatorService()->loadOperators($query, $limit, $cursor);

            $result = new ezpRestMvcResult();
            $resultData = [
                'self' => $this->getBaseUri() . "/operators?query={$query}&limit={$limit}&cursor=" . urlencode($data['current']),
                'items' => $this->openApiTools->replacePlaceholders($data['items']),
                'next' => null,
            ];
            if ($data['next']) {
                $resultData['next'] = $this->getBaseUri() . "/operators?query={$query}&limit={$limit}&cursor=" . rawurlencode($data['next']);
                header("x-next: " . $resultData['next']);
            }
            $result->variables = $resultData;
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doLoadGroups()
    {
        try {
            $query = isset($this->request->get['query']) ? trim($this->request->get['query']) : null;
            $limit = isset($this->request->get['limit']) ? (int)$this->request->get['limit'] : $this->request->variables['limit'];
            $cursor = isset($this->request->get['cursor']) ? rawurldecode($this->request->get['cursor']) : $this->request->variables['cursor'];
            $data = $this->repository->getGroupService()->loadGroups($query, $limit, $cursor);

            $result = new ezpRestMvcResult();
            $resultData = [
                'self' => $this->getBaseUri() . "/groups?query={$query}&limit={$limit}&cursor=" . urlencode($data['current']),
                'items' => $this->openApiTools->replacePlaceholders($data['items']),
                'next' => null,
            ];
            if ($data['next']) {
                $resultData['next'] = $this->getBaseUri() . "/groups?query={$query}&limit={$limit}&cursor=" . rawurlencode($data['next']);
                header("x-next: " . $resultData['next']);
            }
            $result->variables = $resultData;
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doLoadOperatorsAndGroups()
    {
        try {
            $query = isset($this->request->get['query']) ? trim($this->request->get['query']) : null;
            $limit = isset($this->request->get['limit']) ? (int)$this->request->get['limit'] : $this->request->variables['limit'];
            $cursor = isset($this->request->get['cursor']) ? rawurldecode($this->request->get['cursor']) : $this->request->variables['cursor'];
            $data = $this->repository->getSearchService()->searchOperatorAnGroups($query, $limit, $cursor);

            $result = new ezpRestMvcResult();
            $resultData = [
                'self' => $this->getBaseUri() . "/operators?query={$query}&limit={$limit}&cursor=" . urlencode($data['current']),
                'items' => $this->openApiTools->replacePlaceholders($data['items']),
                'next' => null,
            ];
            if ($data['next']) {
                $resultData['next'] = $this->getBaseUri() . "/operators?query={$query}&limit={$limit}&cursor=" . rawurlencode($data['next']);
                header("x-next: " . $resultData['next']);
            }
            $result->variables = $resultData;
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doPostAction()
    {
        try {
            $post = $this->repository->getSearchService()->searchPost($this->Id);
            $payload = $this->getPayload();

            $action = new \Opencontent\Sensor\Api\Action\Action();
            $action->identifier = $this->Action;
            foreach ($payload as $key => $value) {
                $action->setParameter($key, $value);
            }

            $this->repository->getActionService()->runAction($action, $post);
            $result = new ezpRestMvcResult();

            header("HTTP/1.1 204 " . ezpRestStatusResponse::$statusCodes[204]);

        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doPostUpload()
    {
        try {
            $post = $this->repository->getSearchService()->searchPost($this->Id);
            $action = new \Opencontent\Sensor\Api\Action\Action();
            $action->identifier = $this->Action;

            $http = eZHTTPTool::instance();
            $options['upload_dir'] = eZSys::cacheDirectory() . '/fileupload/';
            $options['download_via_php'] = true;
            $options['param_name'] = "files";
            $options['image_versions'] = array();
            $options['max_file_size'] = $http->variable("upload_max_file_size", null);

            $uploadHandler = new SensorBinaryUploadHandler($options, false);
            /** @var array $data */
            $data = $uploadHandler->post(false);
            $files = [];
            foreach ($data[$options['param_name']] as $file) {
                $filePath = $options['upload_dir'] . $file->name;
                $file = eZClusterFileHandler::instance($filePath);
                $files[] = [
                    'filename' => basename($filePath),
                    'file' => base64_encode($file->fetchContents())
                ];
                if ($file->exists()) {
                    $file->delete();
                }
            }
            $action->setParameter('files', $files);

            $this->repository->getActionService()->runAction($action, $post);
            $result = new ezpRestMvcResult();

            header("HTTP/1.1 204 " . ezpRestStatusResponse::$statusCodes[204]);

        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doLoadCategoryTree()
    {
        $result = new ezpRestMvcResult();
        $result->variables = $this->repository->getCategoriesTree()->jsonSerialize();

        return $result;
    }

    public function doLoadAreaTree()
    {
        $result = new ezpRestMvcResult();
        $result->variables = $this->repository->getAreasTree()->jsonSerialize();

        return $result;
    }

    public function doLoadStat()
    {
        $result = new ezpRestMvcResult();
        $stat = $this->repository->getStatisticsService()->getStatisticFactoryByIdentifier($this->Identifier);
        $stat->setParameters($this->request->get);
        $result->variables = $stat->getData();

        return $result;
    }

    protected function getBaseUri()
    {
        $hostUri = $this->request->getHostURI();
        $apiName = ezpRestPrefixFilterInterface::getApiProviderName();
        $apiPrefix = eZINI::instance('rest.ini')->variable('System', 'ApiPrefix');
        $uri = $hostUri . $apiPrefix . '/' . $apiName;

        return $uri;
    }

    protected function getPayload()
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new InvalidArgumentException("Invalid json", 1);
        }
        return $data;
    }

    protected function doExceptionResult(Exception $exception)
    {
        $result = new ezcMvcResult;
        $result->variables['message'] = $exception->getMessage();

        $this->repository->getLogger()->error($exception->getMessage());

        $serverErrorCode = ezpHttpResponseCodes::SERVER_ERROR;
        $errorType = BaseException::cleanErrorCode(get_class($exception));
        if ($exception instanceof BaseException) {
            $serverErrorCode = $exception->getServerErrorCode();
            $errorType = $exception->getErrorType();
        }

        $result->status = new OcOpenDataErrorResponse(
            $serverErrorCode,
            $exception->getMessage(),
            $errorType
        );

        return $result;
    }
}