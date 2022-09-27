<?php

class SensorPreRoutingFilter implements ezpRestPreRoutingFilterInterface
{
    public function __construct( ezcMvcRequest $request )
    {
        if ( strpos( $request->requestId, 'api/sensor' ) !== false )
        {
            eZDebug::setHandleType( eZDebug::HANDLE_FROM_PHP );
            $currentSiteAccess = eZSiteAccess::current();
            $currentSiteAccessName = $currentSiteAccess['name'];
            $sensorRedirectFile = eZSys::rootDir() . "/settings/siteaccess/{$currentSiteAccessName}/sensor_api_redirect";
            if (file_exists($sensorRedirectFile)){
                $sensorSiteAccessName = trim(file_get_contents($sensorRedirectFile));
            }else {
                $sensorSiteAccessName = ObjectHandlerServiceControlSensor::getSensorSiteAccessName();
            }
            if ( $currentSiteAccessName !== $sensorSiteAccessName )
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