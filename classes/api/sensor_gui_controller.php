<?php

use Opencontent\Opendata\Api\Exception\ForbiddenException;
use Opencontent\Sensor\Api\Action\Action;
use Opencontent\Sensor\Api\Exception\BaseException;
use Opencontent\Sensor\Api\Exception\InvalidArgumentException;
use Opencontent\Sensor\Api\Exception\InvalidInputException;
use Opencontent\Sensor\Api\Values\Group;
use Opencontent\Sensor\Api\Values\ParticipantRole;
use Opencontent\Sensor\Legacy\SearchService;
use Opencontent\Sensor\OpenApi;
use Opencontent\Sensor\Legacy\Utils\MimeIcon;

class SensorGuiApiController extends ezpRestMvcController implements SensorOpenApiControllerInterface
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

    public function getBaseUri()
    {
        $hostUri = $this->request->getHostURI();
        $apiName = ezpRestPrefixFilterInterface::getApiProviderName();
        $apiPrefix = eZINI::instance('rest.ini')->variable('System', 'ApiPrefix');
        $uri = $hostUri . $apiPrefix . '/' . $apiName;

        if (eZSys::isSSLNow()) {
            $uri = str_replace('http:', 'https:', $uri);
        }

        return $uri;
    }

    /**
     * @return ezpRestRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    public function doSettings()
    {
        try {
            if ($this->repository->getCurrentUser()->id == eZUser::anonymousId()) {
                throw new \Opencontent\Sensor\Api\Exception\ForbiddenException();
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

    public function doLoadPostByIdWithCapabilities()
    {
        $user = $this->repository->getCurrentUser();
        try {
//            $post = $this->repository->getSearchService()->searchPost($this->Id);
            $post = $this->repository->getPostService()->loadPost($this->Id);
            $result = new ezpRestMvcResult();
            $result->variables = [
                'capabilities' => $this->loadApiUserPostCapabilities($user, $post),
                'post' => $this->loadApiPost($post),
            ];
            $this->repository->getActionService()->runAction(new Action('read'), $post);
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    private function loadApiPost($post)
    {
        $apiPost = $this->openApiTools->replacePlaceholders($post->jsonSerialize());

        $messages = [];
        foreach ($apiPost['timelineItems'] as $message) {
            $message['_type'] = 'system';
            $messages[$message['id']] = $message;
        }
        foreach ($apiPost['privateMessages'] as $message) {
            $message['_type'] = 'private';
            $messages[$message['id']] = $message;
        }
        foreach ($apiPost['comments'] as $message) {
            $message['_type'] = 'public';
            $messages[$message['id']] = $message;
        }
        foreach ($apiPost['responses'] as $message) {
            $message['_type'] = 'response';
            $messages[$message['id']] = $message;
        }
        foreach ($apiPost['audits'] as $message) {
            $message['_type'] = 'audit';
            $messages[$message['id']] = $message;
        }
        ksort($messages);
        $apiPost['_messages'] = array_values($messages);

        return $apiPost;
    }

    public function doLoadPostById()
    {
        if ($this->Id === 'search') {
            return $this->doPostSearch();
        }

        try {
            $post = $this->repository->getSearchService()->searchPost($this->Id);
            $apiPost = $this->loadApiPost($post);
            $this->repository->getActionService()->runAction(new Action('read'), $post);
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
            $parameters = [];
            if (isset($this->request->get['executionTimes'])) {
                $parameters['executionTimes'] = filter_var($this->request->get['executionTimes'], FILTER_VALIDATE_BOOLEAN);
            }
            if (isset($this->request->get['readingStatuses'])) {
                $parameters['readingStatuses'] = filter_var($this->request->get['readingStatuses'], FILTER_VALIDATE_BOOLEAN);
            }
            if (isset($this->request->get['capabilities'])) {
                $parameters['capabilities'] = filter_var($this->request->get['capabilities'], FILTER_VALIDATE_BOOLEAN);
            }
            if (isset($this->request->get['currentUserInParticipants'])) {
                $parameters['currentUserInParticipants'] = filter_var($this->request->get['currentUserInParticipants'], FILTER_VALIDATE_BOOLEAN);
            }
            if (isset($this->request->get['format'])) {
                $parameters['format'] = $this->request->get['format'];
            } else {
                $parameters['format'] = 'json';
            }
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

    public function doLoadCurrentUserLocale()
    {
        try {
            $user = $this->repository->getCurrentUser();
            $result = new ezpRestMvcResult();
            $result->variables = ['locale' => $user->language];
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doPostCurrentUserLocale()
    {
        try {
            $user = $this->repository->getCurrentUser();
            $language = $this->LanguageCode;
            if (!in_array($language, $this->repository->getSensorSettings()->get('SiteLanguages'))){
                throw new InvalidInputException("Language $language not found");
            }

            eZPreferences::setValue('sensor_language', $language, $user->id);
            $result = new ezpRestMvcResult();
            $result->variables = ['locale' => $language];

        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    private function loadApiUserPostCapabilities($user, $post)
    {
        $data = $this->repository->getPermissionService()
            ->loadUserPostPermissionCollection($user, $post)->jsonSerialize();

        $data[] = [
            'identifier' => 'can_behalf_of',
            'grant' => $user->behalfOfMode
        ];
        $data[] = [
            'identifier' => 'is_approver',
            'grant' => !!$post->participants->getParticipantsByRole(ParticipantRole::ROLE_APPROVER)->getUserById($user->id)
        ];
        $data[] = [
            'identifier' => 'is_owner',
            'grant' => !!$post->participants->getParticipantsByRole(ParticipantRole::ROLE_OWNER)->getUserById($user->id)
        ];
        $data[] = [
            'identifier' => 'is_observer',
            'grant' => !!$post->participants->getParticipantsByRole(ParticipantRole::ROLE_OBSERVER)->getUserById($user->id)
        ];
        $data[] = [
            'identifier' => 'is_author',
            'grant' => !!$post->participants->getParticipantsByRole(ParticipantRole::ROLE_AUTHOR)->getUserById($user->id)
        ];
        $data[] = [
            'identifier' => 'has_moderation',
            'grant' => $user->moderationMode
        ];
        $data[] = [
            'identifier' => 'is_a',
            'grant' => $user->type
        ];
        $lastPrivateMessage = -1;
        foreach ($post->privateMessages->messages as $message) {
            if ($message->creator->id == $user->id) {
                $published = $message->published->format('U');
                if ($lastPrivateMessage < $published) {
                    $lastPrivateMessage = $published;
                }
            }
        }
        $data[] = [
            'identifier' => 'last_private_message_timestamp',
            'grant' => $lastPrivateMessage
        ];
        $lastComment = -1;
        foreach ($post->comments->messages as $message) {
            if ($message->creator->id == $user->id) {
                $published = $message->published->format('U');
                if ($lastComment < $published) {
                    $lastComment = $published;
                }
            }
        }
        $data[] = [
            'identifier' => 'last_comment_timestamp',
            'grant' => $lastComment
        ];
        $data[] = [
            'identifier' => 'can_manage',
            'grant' => eZUser::currentUser()->hasAccessTo('sensor', 'manage')['accessWord'] != 'no'
        ];
        $data[] = [
            'identifier' => 'can_read_user',
            'grant' => eZUser::currentUser()->hasAccessTo('sensor', 'user_list')['accessWord'] != 'no'
        ];

        return $data;
    }

    public function doLoadUserPostCapabilities()
    {
        try {
            $result = new ezpRestMvcResult();
            $post = $this->repository->getPostService()->loadPost($this->Id);
            if ($this->UserId == 'current') {
                $user = $this->repository->getCurrentUser();
            } else {
                $user = $this->repository->getUserService()->loadUser($this->UserId);
            }
            $result->variables = $this->loadApiUserPostCapabilities($user, $post);

        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doCreatePost()
    {
        try {
            $controller = new OpenApi\Controller($this->openApiTools, $this);
            $result = $controller->createPost();
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doUpdatePost()
    {
        try {
            $controller = new OpenApi\Controller($this->openApiTools, $this);
            $result = $controller->updatePostById();
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

    public function doLoadOperator()
    {
        try {
            $result = new ezpRestMvcResult();
            $result->variables = $this->repository->getOperatorService()->loadOperator($this->OperatorId);
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

    public function doLoadOperatorsByGroup()
    {
        $result = new ezpRestMvcResult();
        $group = $this->repository->getGroupService()->loadGroup($this->GroupId);
        $operators = [];
        if ($group instanceof Group) {
            $operatorResult = $this->repository->getOperatorService()->loadOperatorsByGroup($group, SearchService::MAX_LIMIT, '*');
            $operators = $operatorResult['items'];
            $this->recursiveLoadOperatorsByGroup($group, $operatorResult, $operators);
        }
        $result->variables = $operators;

        return $result;
    }

    private function recursiveLoadOperatorsByGroup(Group $group, $operatorResult, &$operators)
    {
        if ($operatorResult['next']) {
            $operatorResult = $this->repository->getOperatorService()->loadOperatorsByGroup($group, SearchService::MAX_LIMIT, $operatorResult['next']);
            $operators = array_merge($operatorResult['items'], $operators);
            $this->recursiveLoadOperatorsByGroup($group, $operatorResult, $operators);
        }

        return $operators;
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
            $actions = explode(',', $this->Action);
            $post = $this->repository->getSearchService()->searchPost($this->Id);
            $payload = $this->getPayload();

            foreach ($actions as $action) {
                $action = new Action($action);
                foreach ($payload as $key => $value) {
                    $action->setParameter($key, $value);
                }
                $this->repository->getActionService()->runAction($action, $post);
            }

            $result = $this->doLoadPostByIdWithCapabilities();
            $result->variables['action'] = $actions;

        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function getPayload()
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new InvalidArgumentException("Invalid json", 1);
        }
        return $data;
    }

    public function doPostUpload()
    {
        try {
            $post = $this->repository->getSearchService()->searchPost($this->Id);
            $action = new Action();
            $action->identifier = $this->Action;

            $classAttributeIdentifier = 'sensor_post/attachment';
            if ($action->identifier == 'add_image') {
                $classAttributeIdentifier = 'sensor_post/images';
            }
            if ($action->identifier == 'add_file') {
                $classAttributeIdentifier = 'sensor_post/files';
            }

            $uploadDir = eZSys::cacheDirectory() . '/fileupload/';
            $uploadHandler = $this->getUploadHandler($classAttributeIdentifier, $uploadDir);
            /** @var array $data */
            $data = $uploadHandler->post(false);
            $files = [];
            foreach ($data[$uploadHandler->getOption('param_name')] as $file) {
                if (isset($file->error)) {
                    throw new Exception($file->error);
                }
                $filePath = $uploadHandler->getOption('upload_dir') . $file->name;
                $files[] = [
                    'filename' => basename($filePath),
                    'file' => base64_encode(file_get_contents($filePath))
                ];
                @unlink($filePath);
            }
            $action->setParameter('files', $files);

            $this->repository->getActionService()->runAction($action, $post);
            $result = new ezpRestMvcResult();
            $result->variables = ['action' => $action->identifier];

        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    private function getUploadHandler($classAttributeIdentifier, $uploadDir)
    {
        $options = [];

        if (eZINI::instance('ocmultibinary.ini')->hasVariable('AcceptFileTypesRegex', 'ClassAttributeIdentifier')) {
            $acceptFileTypesClassAttributeIdentifier = eZINI::instance('ocmultibinary.ini')->variable('AcceptFileTypesRegex', 'ClassAttributeIdentifier');
            if (isset($acceptFileTypesClassAttributeIdentifier[$classAttributeIdentifier])) {
                $options['accept_file_types'] = $acceptFileTypesClassAttributeIdentifier[$classAttributeIdentifier];
            }
        }
        $options['upload_dir'] = $uploadDir;
        $options['download_via_php'] = true;
        $options['param_name'] = "files";
        $options['image_versions'] = array();
        $options['max_file_size'] = eZHTTPTool::instance()->variable("upload_max_file_size", null);

        return new SensorBinaryUploadHandler($options, false, [
            1 => ezpI18n::tr('extension/ocmultibinary', 'The uploaded file exceeds the upload_max_filesize directive in php.ini'),
            2 => ezpI18n::tr('extension/ocmultibinary', 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'),
            3 => ezpI18n::tr('extension/ocmultibinary', 'The uploaded file was only partially uploaded'),
            4 => ezpI18n::tr('extension/ocmultibinary', 'No file was uploaded'),
            6 => ezpI18n::tr('extension/ocmultibinary', 'Missing a temporary folder'),
            7 => ezpI18n::tr('extension/ocmultibinary', 'Failed to write file to disk'),
            8 => ezpI18n::tr('extension/ocmultibinary', 'A PHP extension stopped the file upload'),
            'post_max_size' => ezpI18n::tr('extension/ocmultibinary', 'The uploaded file exceeds the post_max_size directive in php.ini'),
            'max_file_size' => ezpI18n::tr('extension/ocmultibinary', 'File is too big'),
            'min_file_size' => ezpI18n::tr('extension/ocmultibinary', 'File is too small'),
            'accept_file_types' => ezpI18n::tr('extension/ocmultibinary', 'Filetype not allowed'),
            'max_number_of_files' => ezpI18n::tr('extension/ocmultibinary', 'Maximum number of files exceeded'),
            'max_width' => ezpI18n::tr('extension/ocmultibinary', 'Image exceeds maximum width'),
            'min_width' => ezpI18n::tr('extension/ocmultibinary', 'Image requires a minimum width'),
            'max_height' => ezpI18n::tr('extension/ocmultibinary', 'Image exceeds maximum height'),
            'min_height' => ezpI18n::tr('extension/ocmultibinary', 'Image requires a minimum height'),
            'abort' => ezpI18n::tr('extension/ocmultibinary', 'File upload aborted'),
            'image_resize' => ezpI18n::tr('extension/ocmultibinary', 'Failed to resize image'),
        ]);
    }

    public function doTempUpload()
    {
        try {
            if (!$this->repository->getPostRootNode()->attribute('can_create')) {
                throw new ForbiddenException('create', 'post');
            }
            $uploadDir = eZSys::varDirectory() . '/fileupload/' . $this->repository->getCurrentUser()->id . '/';
            $uploadHandler = $this->getUploadHandler('sensor_post/' . $this->Identifier, $uploadDir);

            /** @var array $data */
            $data = $uploadHandler->post(false);

            $files = [];
            foreach ($data[$uploadHandler->getOption('param_name')] as $file) {
                if (isset($file->error)) {
                    throw new Exception($file->error);
                }
                $filePath = $uploadHandler->getOption('upload_dir') . $file->name;
                $mime = eZMimeType::findByURL($filePath);
                eZClusterFileHandler::instance($filePath)->storeContents(file_get_contents($filePath));

                if (strpos($mime['name'], 'image') === false){
                    $thumb = base64_encode(file_get_contents(MimeIcon::getIconByMimeType($mime['name'])));
                }else{
                    $thumb = base64_encode(file_get_contents($filePath));
                }

                $files[] = [
                    'mime' => $mime['name'],
                    'filename' => basename($filePath),
                    'filepath' => $filePath,
                    'file' => $thumb,
                ];
                @unlink($filePath);
            }

            $result = new ezpRestMvcResult();
            $result->variables = $files;

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
        try {
            $result = new ezpRestMvcResult();
            $stat = $this->repository->getStatisticsService()->getStatisticFactoryByIdentifier($this->Identifier);
            $stat->setParameters($this->request->get);
            $format = isset($this->request->get['format']) ? $this->request->get['format'] : 'data';
            $result->variables = $stat->getDataByFormat($format);
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doLoadUsers()
    {
        try {
            $controller = new OpenApi\Controller($this->openApiTools, $this);
            $result = $controller->loadUsers();
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doLoadInbox()
    {
        try {
            $result = new ezpRestMvcResult();
            $todolist = new SensorInbox($this->repository);
            $page = isset($this->request->get['page']) ? rawurldecode($this->request->get['page']) : $this->request->variables['page'];
            $limit = isset($this->request->get['limit']) ? rawurldecode($this->request->get['limit']) : $this->request->variables['limit'];
            $filters = isset($this->request->get['filters']) ? $this->request->get['filters'] : [];
            $result->variables = $todolist->get($this->Identifier, $page, $limit, $filters);
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doLoadSpecialIdList()
    {
        $result = new ezpRestMvcResult();
        $result->variables = (new SensorInbox($this->repository))->fetchSpecialIdListForUser($this->repository->getCurrentUser()->id);
        return $result;
    }

    public function doLoadSpecial()
    {
        try {
            $result = new ezpRestMvcResult();
            $this->repository->getSearchService()->searchPost($this->Id);
            $enabled = (bool)$this->Enable;
            $nodes = eZContentObjectTreeNode::fetchByContentObjectID($this->Id);
            $userID = (int)$this->repository->getCurrentUser()->id;
            foreach ($nodes as $node) {
                if ($enabled) {
                    $result->variables[] = eZContentBrowseBookmark::createNew($userID, $node->attribute('node_id'), $node->attribute('name'))->attribute('id');
                } else {
                    $nodeID = (int)$node->attribute('node_id');
                    eZDB::instance()->query("DELETE FROM ezcontentbrowsebookmark WHERE node_id=$nodeID and user_id=$userID");
                }
            }
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doScenarioSearch()
    {
        try {
            if (eZUser::currentUser()->hasAccessTo('sensor', 'config')['accessWord'] == 'no') {
                throw new ForbiddenException('read', 'scenario');
            }

            $parameters = [];
            if (isset($this->request->get['trigger'])) {
                $parameters['trigger'] = $this->request->get['trigger'];
            }
            if (isset($this->request->get['type'])) {
                $parameters['type'] = $this->request->get['type'];
            }
            if (isset($this->request->get['category'])) {
                $parameters['category'] = $this->request->get['category'];
            }
            if (isset($this->request->get['area'])) {
                $parameters['area'] = $this->request->get['area'];
            }
            if (isset($this->request->get['reporter_group'])) {
                $parameters['reporter_group'] = $this->request->get['reporter_group'];
            }

            $result = new ezpRestMvcResult();
            $result->variables = $this->repository->getScenarioService()->searchScenarios($parameters);

        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doCreateScenario()
    {
        try {
            if (eZUser::currentUser()->hasAccessTo('sensor', 'config')['accessWord'] == 'no') {
                throw new ForbiddenException('create', 'scenario');
            }

            $result = new ezpRestMvcResult();
            $result->variables = [$this->repository->getScenarioService()->createScenario($this->getPayload())];
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doEditScenario()
    {
        try {
            if (eZUser::currentUser()->hasAccessTo('sensor', 'config')['accessWord'] == 'no') {
                throw new ForbiddenException('edit', 'scenario');
            }

            $result = new ezpRestMvcResult();
            $result->variables = ['result' => $this->repository->getScenarioService()->editScenario($this->Id, $this->getPayload())];
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doLoadArea()
    {
        try {
            $result = new ezpRestMvcResult();
            $result->variables = $this->repository->getAreaService()->loadArea($this->Id);

        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doPostAreaDisabledCategories()
    {
        try {
            $area = $this->repository->getAreaService()->loadArea($this->Id);

            $result = new ezpRestMvcResult();
            $this->repository->getAreaService()->disableCategories($area, $this->getPayload());

        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doLoadDefaultArea()
    {
        $result = new ezpRestMvcResult();
        $selectedAreaId = eZHTTPTool::instance()->hasSessionVariable(SensorModuleFunctions::SESSION_SELECTED_AREA) ?
            (int)eZHTTPTool::instance()->sessionVariable(SensorModuleFunctions::SESSION_SELECTED_AREA) : 0;
        $result->variables = ['id' => $selectedAreaId];

        return $result;
    }

    public function doPredictCategories()
    {
        $result = new ezpRestMvcResult();
        try {
            $post = $this->repository->getSearchService()->searchPost($this->Id);
            $predictor = SensorCategoryPredictor::instance();
            $result->variables = $predictor->predict($post->id, $post->subject, $post->description);;
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }
        return $result;
    }
}
