<?php

class SensorCollaborationHandler extends eZCollaborationItemHandler
{
    private $repository;

    /**
     * @var SensorTranslationHelper
     */
    private $translations;

    function __construct()
    {
        $this->translations = SensorTranslationHelper::instance();
        $this->repository = OpenPaSensorRepository::instance();
        parent::__construct(
            $this->repository->getSensorCollaborationHandlerTypeString(),
            $this->translations->translate('OpenSegnalazioni Notifications'),
            [
                'use-messages' => true,
                'notification-types' => $this->repository->getNotificationService()->getNotificationTypes(),
                'notification-collection-handling' => eZCollaborationItemHandler::NOTIFICATION_COLLECTION_PER_PARTICIPATION_ROLE,
            ]
        );
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @return eZContentObject
     */
    static function contentObject($collaborationItem)
    {
        $contentObjectID = $collaborationItem->contentAttribute('content_object_id');
        return eZContentObject::fetch($contentObjectID);
    }

    /**
     * @param eZNotificationEvent $event
     * @param eZCollaborationItem $item
     * @param array $parameters
     *
     * @return int
     */
    static function handleCollaborationEvent($event, $item, &$parameters)
    {
        return eZNotificationEventHandler::EVENT_SKIPPED;
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @return string
     */
    function title($collaborationItem)
    {
        return $this->translations->translate('OpenSegnalazioni Notifications');
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @return array|null
     */
    function content($collaborationItem)
    {
        return [
            "content_object_id" => $collaborationItem->attribute("data_int1"),
            "last_change" => $collaborationItem->attribute(\Opencontent\Sensor\Legacy\PostService::COLLABORATION_FIELD_LAST_CHANGE),
            "item_status" => $collaborationItem->attribute(\Opencontent\Sensor\Legacy\PostService::COLLABORATION_FIELD_STATUS),
        ];
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @param bool $viewMode
     */
    function readItem($collaborationItem, $viewMode = false)
    {
        $collaborationItem->setLastRead();
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @return int
     */
    function messageCount($collaborationItem)
    {
        return 0;
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @return int
     */
    function unreadMessageCount($collaborationItem)
    {
        return 0;
    }

    /**
     * @param eZModule $module
     * @param eZCollaborationItem $collaborationItem
     * @return mixed
     */
    function handleCustomAction($module, $collaborationItem)
    {
        return false;
    }

}
