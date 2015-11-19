<?php

interface SensorHelperFactoryInterface
{
    /**
     * @param eZContentObject $contentObject
     *
     * @return SensorPostObjectHelperInterface
     */
    public function getSensorPostObjectHelper( eZContentObject $contentObject );

    /**
     * @param SensorUserInfo $user
     * @param $data
     *
     * @return eZContentObject
     */
    public function sensorPostObjectFactory( SensorUserInfo $user, $data, eZContentObject $update = null );

    /**
     * @return string
     */
    public function getSensorCollaborationHandlerTypeString();

    /**
     * @return array
     */
    public static function getSensorConfigParams();

    /**
     * @return int
     */
    public function getWhatsAppUserId();

    public static function executeWorkflow( $parameters, $process, $event );

    /**
     * @return eZContentObjectTreeNode
     */
    public static function rootNode();

    /**
     * @return eZContentClass
     */
    public static function postContentClass();

    /**
     * @return eZContentObjectTreeNode
     */
    public static function postContainerNode();

    /**
     * @return eZContentObjectTreeNode
     */
    public static function postCategoriesNode();

    /**
     * @return eZContentObjectTreeNode
     */
    public static function operatorsNode();

    /**
     * @param $identifier
     * @return bool
     */
    public static function rootNodeHasAttribute( $identifier );

    /**
     * @return array
     */
    public static function areas();

    /**
     * @param SensorPost|null $post
     *
     * @return array
     */
    public static function operators( SensorPost $post = null );

    /**
     * @param SensorPost|null $post
     *
     * @return array
     */
    public static function observers( SensorPost $post = null );

    /**
     * @return array
     */
    public static function categories();

    /**
     * @return SensorGeoJsonFeatureCollection
     */
    public static function fetchSensorGeoJsonFeatureCollection();

}