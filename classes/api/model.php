<?php

class SensorApiModel extends ezpRestModel
{
    protected static $availableDetails = array(
        'participants',
        'timeline',
        'comments',
        'responses',
        'messages'
    );

    public static function hasDetail( $identifier )
    {
        return in_array( $identifier, self::$availableDetails );
    }

    public static function getLinksByPost( SensorApiPost $post, ezpRestRequest $currentRequest )
    {
        $links = array();
        $baseUri = $currentRequest->getBaseURI();
        $contentQueryString = $currentRequest->getContentQueryString( true );

        foreach( self::$availableDetails as $identifier )
            $links[$identifier] = $baseUri . '/' . $identifier . $contentQueryString;

        return $links;
    }
}