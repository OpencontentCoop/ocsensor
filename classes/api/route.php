<?php

class SensorApiRailsRoute extends ezcMvcRailsRoute
{
    /**
     * Holds protocol string.
     *
     * @var string|null
     */
    protected $protocol;

    /**
     * Constructs a new SensorRailsRoute with $pattern for $protocol.
     *
     * Accepted protocol format: http-get, http-post, http-put, http-delete
     * @see ezcMvcHttpRequestParser::processProtocol();
     *
     * @param string $pattern
     * @param string $controllerClassName
     * @param string $action
     * @param array $defaultValues
     * @param null|string $protocol Match specific protocol if string value, eg: 'http-get'
     */
    public function __construct($pattern, $controllerClassName, $action = null, array $defaultValues = array(), $protocol = null)
    {
        $this->protocol = $protocol;
        parent::__construct($pattern, $controllerClassName, $action, $defaultValues);
    }

    /**
     * Evaluates the URI against this route and protocol.
     *
     * @param ezcMvcRequest $request
     * @return ezcMvcRoutingInformation|null
     */
    public function matches(ezcMvcRequest $request)
    {
        if ($this->match($request, $matches)) {
            if ($request->protocol === $this->protocol) {
                $request->variables = array_merge($this->defaultValues, $request->variables, $matches);

                return new ezcMvcRoutingInformation($this->pattern, $this->controllerClassName, $this->action);
            }
        }

        return null;
    }
}