<?php

use Opencontent\Opendata\Api\Values\SearchResults;
use Opencontent\Sensor\Api\Values\Participant;
use Opencontent\Sensor\Api\Values\Post;
use Opencontent\Sensor\Legacy\Repository;
use Opencontent\Sensor\Legacy\Utils;

class SensorInbox
{
    private $repository;

    private $page = 1;

    private $limit = 10;

    private $filterQuery = '';

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function get($identifier, $page = 1, $limit = 10, $filters = [])
    {
        $this->page = $page;
        $this->limit = $limit;
        $filtersQueries = [];
        foreach ($filters as $filter){
            if (isset($filter['value']) && !empty($filter['value'])){
                $filtersQueries[] = $filter['field'] . " = '" .  $filter['value'] . "'";
            }
        };
        if (!empty($filtersQueries)) {
            $this->filterQuery = ' and ' . implode(' and ', $filtersQueries) . ' and ';
        }

        switch ($identifier) {
            case 'todolist':
                return $this->getTodolist();

            case 'special':
                return $this->getSpecial();

            case 'conversations':
                return $this->getConversations();

            case 'moderate':
                return $this->getModerate();

            case 'closed':
                return $this->getClosed();
        }

        throw new RuntimeException("Invalid inbox identifier: $identifier");
    }

    private function getTodolist()
    {
        $page = $this->page;
        $limit = $this->limit;
        $currentUserId = $this->repository->getCurrentUser()->id;
        $unreadCommentField = "user_{$currentUserId}_unread_comments";
        if ($this->repository->getSensorSettings()->get('ShowInboxAllPrivateMessage')){
            $unreadPrivateField = "user_{$currentUserId}_unread_private_messages";
        }else {
            $unreadPrivateField = "user_{$currentUserId}_unread_private_messages_as_receiver";
        }

        $specialIdList = $this->fetchSpecialIdListForUser($currentUserId);
        $specialQuery = '';
        if (count($specialIdList) > 0) {
            $specialQuery = 'or (id in [' . implode(',', $specialIdList) . '])';
        }
        $query = "(
            (approver_id_list = '$currentUserId' and workflow_status in ['waiting','read','reopened','fixed'])
            or (owner_id_list = '$currentUserId' and owner_user_id_list = '$currentUserId' and workflow_status in ['assigned'])
            or (owner_id_list = '$currentUserId' and owner_user_id_list !range [*,*] and workflow_status in ['assigned']) 
            or ((approver_id_list = '$currentUserId' or owner_id_list = '$currentUserId') and workflow_status in ['assigned','fixed','closed'] and $unreadPrivateField range [1,*]) 
            or (approver_id_list = '$currentUserId' and unmoderated_comments range [1,*])
            or ((approver_id_list = '$currentUserId' or owner_user_id_list = '$currentUserId') and $unreadCommentField range [1,*]) 
            $specialQuery           
        ) {$this->filterQuery} sort [modified=>desc]";

        $query .= " limit $limit offset " . ($page - 1) * $limit;

        $results = $this->repository->getSearchService()->searchPosts(
            $query,
            [
                'readingStatuses' => true,
                'currentUserInParticipants' => true,
                'capabilities' => true,
            ]
        );
        $data = $this->serializeSearchResult('todolist', $results, $specialIdList);

        return [
            'current_page' => (int)$page,
            'pages' => ceil($results->totalCount / $limit),
            'items' => $data,
            'count' => $results->totalCount,
        ];
    }

    private function getSpecial()
    {
        $page = $this->page;
        $limit = $this->limit;
        $currentUserId = $this->repository->getCurrentUser()->id;
        $specialIdList = $this->fetchSpecialIdListForUser($currentUserId);
        if (count($specialIdList) > 0) {
            $query = 'id in [' . implode(',', $specialIdList) . '] sort [modified=>desc]';
            $query .= "{$this->filterQuery} limit $limit offset " . ($page - 1) * $limit;
            $results = $this->repository->getSearchService()->searchPosts(
                $query,
                [
                    'readingStatuses' => true,
                    'currentUserInParticipants' => true,
                    'capabilities' => true,
                ]
            );
            $data = $this->serializeSearchResult('special', $results, $specialIdList);
            $count = $results->totalCount;
        } else {
            $data = [];
            $count = 0;
        }

        return [
            'current_page' => (int)$page,
            'pages' => $count > 0 ? ceil($count / $limit) : $count,
            'items' => $data,
            'count' => $count,
        ];
    }

    private function getConversations()
    {
        $page = $this->page;
        $limit = $this->limit;
        $currentUserId = $this->repository->getCurrentUser()->id;
        $unreadPrivateField = "user_{$currentUserId}_private_messages";

        $specialIdList = $this->fetchSpecialIdListForUser($currentUserId);
        $query = "$unreadPrivateField range [1,*] sort [modified=>desc]";
        $query .= "{$this->filterQuery}  limit $limit offset " . ($page - 1) * $limit;

        $results = $this->repository->getSearchService()->searchPosts(
            $query,
            [
                'readingStatuses' => true,
                'currentUserInParticipants' => true,
                'capabilities' => true,
            ]
        );
        $data = $this->serializeSearchResult('conversations', $results, [$specialIdList]);

        return [
            'current_page' => (int)$page,
            'pages' => ceil($results->totalCount / $limit),
            'items' => $data,
            'count' => $results->totalCount,
        ];
    }

    private function getModerate()
    {
        $page = $this->page;
        $limit = $this->limit;
        $currentUserId = $this->repository->getCurrentUser()->id;
        $specialIdList = $this->fetchSpecialIdListForUser($currentUserId);

        $query = "approver_id_list = '$currentUserId' and unmoderated_comments range [1,*] sort [modified=>desc]";
        $query .= "{$this->filterQuery} limit $limit offset " . ($page - 1) * $limit;

        $results = $this->repository->getSearchService()->searchPosts(
            $query,
            [
                'readingStatuses' => true,
                'currentUserInParticipants' => true,
                'capabilities' => true,
            ]
        );
        $data = $this->serializeSearchResult('moderate', $results, [$specialIdList]);

        return [
            'current_page' => (int)$page,
            'pages' => ceil($results->totalCount / $limit),
            'items' => $data,
            'count' => $results->totalCount,
        ];
    }

    private function getClosed()
    {
        $page = $this->page;
        $limit = $this->limit;
        $currentUserId = $this->repository->getCurrentUser()->id;
        $specialIdList = $this->fetchSpecialIdListForUser($currentUserId);

        $query = "workflow_status = 'closed' sort [modified=>desc]";
        $query .= "{$this->filterQuery} limit $limit offset " . ($page - 1) * $limit;

        $results = $this->repository->getSearchService()->searchPosts(
            $query,
            [
                'readingStatuses' => true,
                'currentUserInParticipants' => true,
                'capabilities' => true,
            ]
        );
        $data = $this->serializeSearchResult('moderate', $results, [$specialIdList]);

        return [
            'current_page' => (int)$page,
            'pages' => ceil($results->totalCount / $limit),
            'items' => $data,
            'count' => $results->totalCount,
        ];
    }

    public function fetchSpecialIdListForUser($userID, $offset = false, $limit = false)
    {
        $userID = (int)$userID;
        $query = "SELECT ezcontentobject_tree.contentobject_id as id 
                    FROM ezcontentobject_tree INNER JOIN ezcontentbrowsebookmark ON (ezcontentobject_tree.node_id = ezcontentbrowsebookmark.node_id)
                    WHERE ezcontentbrowsebookmark.user_id = $userID";
        $list = eZDB::instance()->arrayQuery($query);

        $list = array_column($list, 'id');
        return array_map('intval', $list);
    }

    private function serializeSearchResult($context, SearchResults $results, $specialIdList)
    {
        $userId = $this->repository->getCurrentUser()->id;
        $groupIdList = $this->repository->getCurrentUser()->groups;

        $data = [];
        /** @var Post $post */
        foreach ($results->searchHits as $post) {
            $contextActions = [];
            $lastUserAccess = $post->readingStatuses['last_access_timestamp'];
            $actions = [];
            if (!(bool)$post->readingStatuses['has_read']
                || $post->workflowStatus->identifier === 'waiting') {
                $actions[] = 'read_post';
            }
            if ($post->workflowStatus->identifier === 'read'
                || $post->workflowStatus->identifier === 'reopened') {
                $actions[] = 'assign_post';
            } elseif ($post->workflowStatus->identifier === 'fixed') {
                $actions[] = 'respond_and_close_post';
            } elseif ($post->workflowStatus->identifier === 'assigned') {
                if (in_array($userId, $post->owners->getParticipantIdListByType(Participant::TYPE_USER))){
                    $actions[] = 'fix_post';
                }else{
                    foreach ($groupIdList as $groupId){
                        if (in_array($groupId, $post->owners->getParticipantIdListByType(Participant::TYPE_GROUP))){
                            $actions[] = 'autoassign_post';
                            break;
                        }
                    }
                }
            }

            $messageCount = 1;
            $people = [[
                'id' => (int)$post->author->id,
                'name' => $post->author->name,
                'is_read' => (bool)$post->readingStatuses['has_read'],
                'published' => (int)$post->published->format('U')
            ]];
            foreach ($post->privateMessages->messages as $message) {
                $doCount = true;
                if ($context == 'todolist'){
                    if (!$this->repository->getSensorSettings()->get('ShowInboxAllPrivateMessage')) {
                        $doCount = $message->getReceiverById($userId);
                        if (!$doCount) {
                            foreach ($groupIdList as $groupId) {
                                if (!$doCount) {
                                    $doCount = $message->getReceiverById($groupId);
                                }
                            }
                        }
                    }
                }
                if ($doCount) {
                    $isRead = $message->published->format('U') < $lastUserAccess;
                    if (!$isRead) {
                        $actions[] = 'read_private_message';
                    }
                    $people[] = [
                        'id' => (int)$message->creator->id,
                        'name' => $message->creator->name,
                        'is_read' => $isRead,
                        'published' => (int)$message->published->format('U')
                    ];
                }
                $messageCount++;
            }
            foreach ($post->comments->messages as $message) {
                $isRead = $message->published->format('U') < $lastUserAccess;
                if (!$isRead) {
                    $actions[] = 'read_comment';
                }
                $people[] = [
                    'id' => (int)$message->creator->id,
                    'name' => $message->creator->name,
                    'is_read' => $isRead,
                    'published' => (int)$message->published->format('U'),
                ];
                if ($message->needModeration) {
                    $actions[] = 'moderate_comment';
                }
                $messageCount++;
            }

            usort($people, function ($a, $b) {
                if ($a['published'] == $b['published']) {
                    return 0;
                }
                return ($a['published'] < $b['published']) ? -1 : 1;
            });

            $uniquePeople = [];
            foreach ($people as $person) {
                if (isset($uniquePeople[$person['id']])) {
                    $uniquePeople[$person['id']]['count']++;
                    if (!$person['is_read']) {
                        $uniquePeople[$person['id']]['is_read'] = false;
                    }
                }
                $uniquePeople[$person['id']] = [
                    'id' => (int)$person['id'],
                    'name' => $person['name'],
                    'is_read' => $person['is_read'],
                    'count' => 1
                ];
            }

            $actions = array_values(array_unique($actions));
            $action = array_pop($actions);
            if ($action) {
                $actions[] = $action;
            }

            if (in_array('respond_and_close_post', $actions)){
                $contextActions[] = [
                    'identifier' => 'context-close',
                    'data' => [
                        'last_private_note' => $post->privateMessages->last()
                    ]
                ];
            }

            $data[] = [
                'id' => (int)$post->id,
                'is_special' => in_array($post->id, $specialIdList),
                'subject' => $post->subject,
                'modified_datetime' => $this->formatDate($post->modified),
                'modified_at' => Utils::getDateDiff($post->modified)->getText(),
                'people' => $uniquePeople,
                'people_html' => $this->getPeopleHtml($uniquePeople),
//                'all_people' => $people,
                'conversations' => $messageCount,
                'workflowStatus' => $post->workflowStatus->identifier,
                'status' => $post->status->identifier,
//                'reading_status' => $post->readingStatuses,
                'has_read' => (bool)$post->readingStatuses['has_read'],
                'actions' => $actions,
                'action' => $this->getActionText($action),
                'contextActions' => $this->repository->getSensorSettings()->get('UseInboxContextActions') ? $contextActions : [],
                'category' => count($post->categories) > 0 ? $post->categories[0]->name: null,
                'area' => count($post->areas) > 0 ? $post->areas[0]->name : null,
                'owners' => [
                    'group' => $post->owners->getParticipantIdListByType(Participant::TYPE_GROUP),
                    'users' => $post->owners->getParticipantIdListByType(Participant::TYPE_USER),
                ],
                'expirationInfo' => $post->expirationInfo
            ];
        }

        return $data;
    }

    private function getActionText($action)
    {
        $map = [
            'read_post' => 'Nuova segnalazione',
            'assign_post' => 'Segnalazione da assegnare',
            'respond_and_close_post' => 'Fine lavorazione',
            'fix_post' => 'Segnalazione in lavorazione',
            'autoassign_post' => 'Segnalazione da prendere in carico',
            'read_private_message' => 'Nuova nota privata',
            'read_comment' => 'Nuovo commento',
            'moderate_comment' => 'Nuovo commento da moderare',
        ];

        if (isset($map[$action])){
            return $map[$action];
        }

        return $action;
    }

    private function getPeopleHtml($uniquePeople)
    {
        $peopleAsHtml = [];
        $firstSet = false;
        $firstUnreadSet = false;
        $lastSet = false;
        foreach ($uniquePeople as $person){
            $name = $person['name'];
            if (count($uniquePeople) > 2){
                $name = explode(' ', $name)[0];
            }
            if (!$person['is_read']){
                $name = "<strong>{$name}</strong>";
            }
            if (count($uniquePeople) > 3) {
                if (!$firstSet) {
                    $firstSet = true;
                    $peopleAsHtml[] = $name;
                    $lastSet = $name;
                }else{
                    if (!$person['is_read'] && !$firstUnreadSet){
                        $firstUnreadSet = true;
                        $peopleAsHtml[] = $name;
                        $lastSet = $name;
                    }else{
                        if ($lastSet !== '..') {
                            $peopleAsHtml[] = '..';
                            $lastSet = '..';
                        }
                    }
                }
            }else{
                $peopleAsHtml[] = $name;
            }
        }

        return count($uniquePeople) > 3 ? implode(' ', $peopleAsHtml) : implode(', ', $peopleAsHtml);
    }

    private function formatDate($dateTime)
    {
        if ($dateTime instanceof \DateTime) {
            return $dateTime->format('c');
        }
        return null;
    }
}
