<?php

namespace OpenContent\Sensor\Api;

use OpenContent\Sensor\Api\Values\User;
use OpenContent\Sensor\Api\Values\Post;
use OpenContent\Sensor\Api\PostService;
use OpenContent\Sensor\Api\MessageService;
use OpenContent\Sensor\Api\SearchService;
use OpenContent\Sensor\Api\ParticipantService;
use OpenContent\Sensor\Api\PermissionService;
use OpenContent\Sensor\Api\ActionService;
use OpenContent\Sensor\Api\UserService;

interface Repository
{

    /**
     * @return User
     */
    public function getCurrentUser();

    public function setCurrentUser( User $user );

    public function getCurrentLanguage();

    public function setCurrentLanguage( $language );

    public function isUserParticipant( Post $post );

    /**
     * @return PostService
     */
    public function getPostService();

    /**
     * @return MessageService
     */
    public function getMessageService();

    /**
     * @return SearchService
     */
    public function getSearchService();

    /**
     * @return ParticipantService
     */
    public function getParticipantService();

    /**
     * @return PermissionService
     */
    public function getPermissionService();

    /**
     * @return ActionService
     */
    public function getActionService();

    /**
     * @return UserService
     */
    public function getUserService();
}