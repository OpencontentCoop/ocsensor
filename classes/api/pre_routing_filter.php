<?php

class SensorPreRoutingFilter implements ezpRestPreRoutingFilterInterface
{
    public function __construct( ezcMvcRequest $request )
    {        
        if ( strpos( $request->requestId, 'api/sensor' ) !== false )
        {                                    
            eZDebug::setHandleType( eZDebug::HANDLE_FROM_PHP );
            $currentSiteaccess = eZSiteAccess::current();
            $sensorSiteAccessName = ObjectHandlerServiceControlSensor::getSensorSiteAccessName();
            if ( $currentSiteaccess['name'] !== $sensorSiteAccessName )
            {
                eZINI::resetAllInstances();            
                eZExtension::activateExtensions( 'default' );            
                eZSiteAccess::change( array( 'name' => $sensorSiteAccessName ) );            
                eZExtension::activateExtensions( 'access' );
            }
        }
    }

    public function filter()
    {
    }

}
