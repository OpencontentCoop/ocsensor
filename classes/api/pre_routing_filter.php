<?php

class SensorPreRoutingFilter implements ezpRestPreRoutingFilterInterface
{
    public function __construct( ezcMvcRequest $request )
    {
        if ( strpos( $request->requestId, 'api/sensor/' ) !== false )
        {
            eZDebug::setHandleType( eZDebug::HANDLE_FROM_PHP );
            eZINI::resetAllInstances();
            eZExtension::activateExtensions( 'default' );
            eZSiteAccess::change( array( 'name' => ObjectHandlerServiceControlSensor::getSensorSiteAccessName() ) );
            eZExtension::activateExtensions( 'access' );
        }
    }

    public function filter()
    {
    }

}
