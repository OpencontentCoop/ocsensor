<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 23/11/15
 * Time: 22:44
 */

namespace OpenContent\Sensor\Legacy;

use OpenContent\Sensor\Legacy\PostService;
use OpenContent\Sensor\Api\Values\Post;
use eZCollaborationItem;
use eZContentObject;
use eZDir;
use eZSys;
use eZClusterFileHandler;
use eZINI;

class CachePostService extends PostService
{
    public function loadPost( $postId )
    {
        return self::cacheFileHandler( $postId )->processCache(
            array( 'OpenContent\Sensor\Legacy\CachePostService', 'cacheRetrieve' ),
            array( 'OpenContent\Sensor\Legacy\CachePostService', 'cacheGenerate' ),
            null,
            null,
            array( $postId, get_class( $this->repository ) )
        );
    }

    public function refreshPost( Post $post )
    {
        self::clearCache( $post->id );
    }

    public static function cacheRetrieve( $file, $mtime, $args )
    {
        $result = include( $file );
        return $result;
    }

    public static function cacheGenerate( $file, $args )
    {
        list( $postId, $repositoryClassName ) = $args;
        $repository = new $repositoryClassName();
        $service = new PostService( $repository );
        $post = $service->loadPost( $postId );
        return array( 'content'  => $post,
                      'scope'    => 'sensor-post-cache',
                      'datatype' => 'php',
                      'store'    => true );

    }

    public static function clearCache( $postId )
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
            $commonPath = eZDir::path( array( eZSys::cacheDirectory(), 'sensor' ) );
            $fileHandler = eZClusterFileHandler::instance();
            $commonSuffix = "post-object/" . eZDir::filenamePath( $postId );
            $fileHandler->fileDeleteByDirList( $siteAccesses, $commonPath, $commonSuffix );
        }
    }

    public static function cacheFileHandler( $postId )
    {
        $cacheFile = $postId . '.cache';
        $currentSiteAccess = $GLOBALS['eZCurrentAccess']['name'];
        $extraPath = eZDir::filenamePath( $postId );
        $cacheFilePath = eZDir::path( array( eZSys::cacheDirectory(), 'sensor', $currentSiteAccess, 'post-object', $extraPath, $cacheFile ) );
        return eZClusterFileHandler::instance( $cacheFilePath );
    }
}