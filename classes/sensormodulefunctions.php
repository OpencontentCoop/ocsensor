<?php

class SensorModuleFunctions
{
    const GLOBAL_PREFIX = 'global-';
    
    public static function onClearObjectCache( $nodeList )
    {
        return $nodeList;
    }
    
    protected static function clearSensorCache( $prefix )
    {
        $ini = eZINI::instance();
        if ( $ini->hasVariable( 'SiteAccessSettings', 'RelatedSiteAccessList' ) &&
             $relatedSiteAccessList = $ini->variable( 'SiteAccessSettings', 'RelatedSiteAccessList' ) )
        {
            if ( !is_array( $relatedSiteAccessList ) )
            {
                $relatedSiteAccessList = array( $relatedSiteAccessList );
            }
            $relatedSiteAccessList[] = $GLOBALS['eZCurrentAccess']['name'];
            $siteAccesses = array_unique( $relatedSiteAccessList );
        }
        else
        {
            $siteAccesses = $ini->variable( 'SiteAccessSettings', 'AvailableSiteAccessList' );
        }            
        if ( !empty( $siteAccesses ) )
        {                
            $cacheBaseDir = eZDir::path( array( eZSys::cacheDirectory(), 'sensor' ) );                
            $fileHandler = eZClusterFileHandler::instance();
            $fileHandler->fileDeleteByDirList( $siteAccesses, $cacheBaseDir, $prefix );
        }
    }
    
    public static function sensorHomeGenerate( $file, $args )
    {
        $currentUser = eZUser::currentUser();
        
        $tpl = eZTemplate::factory();        
        $tpl->setVariable( 'current_user', $currentUser );
        $tpl->setVariable( 'persistent_variable', array() );
        $tpl->setVariable( 'sensor_home', true );
        
        $Result = array();
        $Result['persistent_variable'] = $tpl->variable( 'persistent_variable' );
        $Result['content'] = $tpl->fetch( 'design:sensor/home.tpl' );
        $Result['node_id'] = 0;
        
        $contentInfoArray = array( 'url_alias' => 'sensor/home' );
        $contentInfoArray['persistent_variable'] = array( 'sensor_home' => true );
        if ( $tpl->variable( 'persistent_variable' ) !== false )
        {
            $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );
            $contentInfoArray['persistent_variable']['sensor_home'] = true;
        }
        $Result['content_info'] = $contentInfoArray;
        $Result['path'] = array();
        $returnValue = array( 'content' => $Result,
                         'scope'   => 'sensor' );
        return $returnValue;
    }
    
    public static function sensorInfoGenerate( $file, $args )
    {
        extract( $args );
        if ( isset( $Params ) && $Params['Module'] instanceof eZModule )
        {
            $tpl = eZTemplate::factory();
            $identifier = $Params['Page'];
            $repository = OpenPaSensorRepository::instance();
            if ( $identifier && $repository->getRootNodeAttribute( $identifier ) )
            {
                $currentUser = eZUser::currentUser();

                $tpl->setVariable( 'current_user', $currentUser );
                $tpl->setVariable( 'persistent_variable', array() );
                $tpl->setVariable( 'identifier', $identifier );
                $tpl->setVariable( 'attribute', $repository->getRootNodeAttribute( $identifier ) );

                $Result = array();

                $Result['persistent_variable'] = $tpl->variable( 'persistent_variable' );
                $Result['content'] = $tpl->fetch( 'design:sensor/info.tpl' );
                $Result['node_id'] = 0;

                $contentInfoArray = array( 'url_alias' => 'sensor/info' );
                $contentInfoArray['persistent_variable'] = false;
                if ( $tpl->variable( 'persistent_variable' ) !== false )
                {
                    $contentInfoArray['persistent_variable'] = $tpl->variable(
                        'persistent_variable'
                    );
                }
                $Result['content_info'] = $contentInfoArray;
                $Result['path'] = array();

                $returnValue = array(
                    'content' => $Result,
                    'scope' => 'sensor'
                );
            }
            else
            {
                /** @var eZModule $module */
                $module = $Params['Module'];
                $returnValue = array(
                    'content' => $module->handleError(
                        eZError::KERNEL_NOT_AVAILABLE,
                        'kernel'
                    ),
                    'store' => false
                );
            }
        }
        else
        {
            $returnValue = array(
                'content' => 'error',
                'store' => false
            );
        }
        return $returnValue;
    }    
    
    public static function sensorGlobalCacheFilePath( $fileName )
    {
        $currentSiteAccess = $GLOBALS['eZCurrentAccess']['name'];
        $cacheFile = self::GLOBAL_PREFIX . $fileName . '.php';
        $cachePath = eZDir::path( array( eZSys::cacheDirectory(), 'sensor', $currentSiteAccess, $cacheFile ) );        
        return $cachePath;
    }

}
