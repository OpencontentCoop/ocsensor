<?php

use Opencontent\Sensor\Api\Exception\BaseException;
use Opencontent\Sensor\Api\Exception\InvalidArgumentException;
use Opencontent\Sensor\OpenApi;

class SensorOpenApiController extends ezpRestMvcController
{
    /**
     * @var ezpRestRequest
     */
    protected $request;

    /**
     * @var \Opencontent\Sensor\Legacy\Repository
     */
    private $repository;

    /**
     * @var \Opencontent\Sensor\OpenApi
     */
    private $openApiTools;

    private $baseUri;

    public function __construct($action, ezcMvcRequest $request)
    {
        parent::__construct($action, $request);

        $hostUri = $this->request->getHostURI();
        $hostUri = str_replace('http:/', 'https:/', $hostUri); //@todo
        $apiName = ezpRestPrefixFilterInterface::getApiProviderName();
        $apiPrefix = eZINI::instance('rest.ini')->variable('System', 'ApiPrefix');
        $this->baseUri = $hostUri . $apiPrefix . '/' . $apiName;

        $this->repository = OpenPaSensorRepository::instance();
        $this->openApiTools = new OpenApi(
            $this->repository,
            $this->getHostURI(),
            $this->getBaseUri()
        );
    }

    private function getHostURI()
    {
        $hostUri = $this->request->getHostURI();
        if (eZSys::isSSLNow()){
            $hostUri = str_replace('http:', 'https:', $hostUri);
        }

        return $hostUri;
    }

    public function doEndpoint()
    {
        $schema = $this->openApiTools->loadSchema();
        $result = new ezpRestMvcResult();
        $result->variables = $schema;

        return $result;
    }

    public function doAction()
    {
        try {

            $controller = new OpenApi\Controller($this->openApiTools, $this);

            if (!method_exists($controller, $this->request->variables['operationId'])){
                throw new InvalidArgumentException("Invalid operationId " . $this->request->variables['operationId'], 1);
            }
            return $controller->{$this->request->variables['operationId']}();
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * @return ezpRestRequest
     */
    public function getRequest()
    {
        return $this->request;
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

    private function doExceptionResult(Exception $exception)
    {
        $result = new ezcMvcResult;
        $result->variables['message'] = $exception->getMessage();

        $this->repository->getLogger()->error($exception->getMessage() . PHP_EOL . $exception->getTraceAsString(), ['api_request' => $_SERVER['QUERY_STRING']]);

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