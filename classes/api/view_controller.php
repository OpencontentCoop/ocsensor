<?php

class SensorApiViewController implements ezpRestViewControllerInterface
{
    /**
     * Creates a view required by controller's result
     *
     * @param ezcMvcRoutingInformation $routeInfo
     * @param ezcMvcRequest $request
     * @param ezcMvcResult $result
     * @return ezcMvcView
     */
    public function loadView( ezcMvcRoutingInformation $routeInfo, ezcMvcRequest $request, ezcMvcResult $result )
    {
        return new SensorApiJsonView( $request, $result );
    }
}
