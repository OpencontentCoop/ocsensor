<?php

use Opencontent\Sensor\Api\Values\Message\Comment;
use Opencontent\Sensor\Api\Values\Message\CommentCollection;
use Opencontent\Sensor\Api\Values\Message\PrivateMessage;
use Opencontent\Sensor\Api\Values\Message\PrivateMessageCollection;
use Opencontent\Sensor\Api\Values\Message\Response;
use Opencontent\Sensor\Api\Values\Message\ResponseCollection;
use Opencontent\Sensor\Api\Values\Message\TimelineItem;
use Opencontent\Sensor\Api\Values\Message\TimelineItemCollection;
use Opencontent\Sensor\Api\Values\Participant;
use Opencontent\Sensor\Api\Values\ParticipantCollection;
use Opencontent\Sensor\Api\Values\User;
use Opencontent\Sensor\OpenApi;
use Opencontent\Sensor\OpenApi\PostSerializer;
use Opencontent\Sensor\Api\Values\Post;

class SensorWebHookTrigger implements OCWebHookTriggerInterface, OCWebHookCustomPayloadSerializerInterface, OCWebHookCustomEndpointSerializerInterface
{
    protected static $schema;

    protected $identifier;

    protected $name;

    protected $repository;

    private $postSerializer;

    /**
     * @var OpenApi
     */
    protected $api;

    public function __construct($identifier, $name)
    {
        $this->identifier = $identifier;
        $this->name = $name;
        $this->repository = OpenPaSensorRepository::instance();
        $siteUrl = '/';
        eZURI::transformURI($siteUrl,true, 'full');
        $endpointUrl = '/api/sensor';
        eZURI::transformURI($endpointUrl, true, 'full');
        $this->api = new OpenApi(
            $this->repository,
            $siteUrl,
            $endpointUrl
        );
        $this->postSerializer = new PostSerializer($this->api);
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getDescription()
    {
        return "Viene scatenato quando accade un evento di tipo '" . $this->getName() . "'. Il payload di default è un oggetto json API Sensor Post";
    }

    public function getName()
    {
        return $this->name;
    }

    public function canBeEnabled()
    {
        return true;
    }

    public function useFilter()
    {
        if ($this->identifier === 'on_create') {
            return false;
        }

        if (self::$schema === null) {
            $categories = $this->repository->getCategoriesTree();
            $categoryList = [];
            foreach ($categories->attribute('children') as $category) {
                $categoryList['cat-' . $category->attribute('id')] = '<strong>' . str_replace("'", "&apos;", $category->attribute('name')) . '</strong>';
                foreach ($category->attribute('children') as $child) {
                    $categoryList['cat-' . $child->attribute('id')] = str_replace("'", "&apos;", $child->attribute('name'));
                }
            }

            $groups = $this->repository->getGroupsTree();
            $groupList = [];
            foreach ($groups->attribute('children') as $group) {
                $groupList['group-' . $group->attribute('id')] = str_replace("'", "&apos;", $group->attribute('name'));
            }

            $schema = [
                'schema' => [
                    'title' => "Il webhook viene eseguito quando si verificano tutte le condizioni di seguito indicate",
                    'type' => 'object',
                    'properties' => [
                        'group' => [
                            'title' => "1 - Seleziona un gruppo",
                            'type' => 'string',
                            'enum' => array_keys($groupList),
                        ],
                        'category' => [
                            'title' => "2 - Seleziona una o più categorie",
                            'type' => 'string',
                            'enum' => array_keys($categoryList),
                        ],
                    ],
                ],
                'options' => [
                    'fields' => [
                        'category' => [
                            'helper' => "Il webhook viene eseguito solo se la segnalazione rientra nelle categorie selezionate",
                            'optionLabels' => array_values($categoryList),
                            'multiple' => true,
                            'type' => 'checkbox',
                            'sort' => false,
                        ],
                        'group' => [
                            'helper' => "Il webhook viene eseguito solo se la segnalazione è assegnata a un gruppo selezionato",
                            'optionLabels' => array_values($groupList),
                            'type' => 'select',
                        ],
                    ],
                ]
            ];

            self::$schema = json_encode($schema);
        }

        return self::$schema;
    }

    /* 
     * Code to test:
     * $repository = OpenPaSensorRepository::instance();
     * $post = $repository->getPostService()->loadPost(124);
     * $siteUrl = '/';
     * eZURI::transformURI($siteUrl,true, 'full');
     * $endpointUrl = '/api/sensor';
     * eZURI::transformURI($endpointUrl, true, 'full');
     * $openApiTools = new \Opencontent\Sensor\OpenApi(
     *     $repository,
     *     $siteUrl,
     *     $endpointUrl
     * );
     * $postSerializer = new \Opencontent\Sensor\OpenApi\PostSerializer($openApiTools);
     * OCWebHookEmitter::emit('on_assign', $postSerializer->serialize($post), OCWebHookQueue::HANDLER_IMMEDIATE);
    */
    public function isValidPayload($post, $filters)
    {
        if ($this->identifier === 'on_create' || empty($filters)) {
            return true;
        }

        $filters = json_decode($filters, true);
        $categoryIdList = $groupIdList = [];
        if (isset($filters['category'])) {
            $categoryIdList = explode(',', $filters['category']);
        }
        if (isset($filters['group'])) {
            $groupIdList = explode(',', $filters['group']);
        }

        if (empty($categoryIdList) && empty($groupIdList)) {
            return true;
        }

        try {
            $filteredByCategory = false;
            if (!empty($categoryIdList)) {
                foreach ($post->categories as $category) {
                    if (in_array('cat-' . $category->id, $categoryIdList)) {
                        $filteredByCategory = true;
                        break;
                    }
                }
            } else {
                $filteredByCategory = true;
            }

            $filteredByGroup = false;
            if (!empty($groupIdList)) {
                foreach ($post->owners->getParticipantIdListByType(Participant::TYPE_GROUP) as $groupId) {
                    if (in_array('group-' . $groupId, $groupIdList)) {
                        $filteredByGroup = true;
                        break;
                    }
                }
            } else {
                $filteredByGroup = true;
            }

            return $filteredByCategory && $filteredByGroup;

        } catch (Exception $e) {
            eZDebug::writeError($e->getMessage(), __METHOD__);
        }

        return false;
    }

    /*
     * Code to test:
	 * $post = OpenPaSensorRepository::instance()->getPostService()->loadPost(289);
	 * $trigger = new SensorWebHookTrigger('test', 'Test');
	 * $webhook = OCWebHook::fetch(3);
	 * $payload = $trigger->serializeCustomPayload($post, $webhook);
	 * print_r($webhook->getCustomPayloadParameters());
	 * print_r($payload);
     */
    public function serializeCustomPayload($originalPayload, OCWebHook $webHook)
    {
        if (!$originalPayload instanceof Post){
            return $originalPayload;
        }

        if ($webHook->hasCustomPayloadParameters()){
            if ($customPayload = $this->compileCustomPayload($originalPayload, $webHook->getCustomPayloadParameters())){
                return $customPayload;
            }
        }

        return $this->postSerializer->serialize($originalPayload);
    }

    public function getPlaceholders()
    {
        $post = $this->postSerializer->serialize($this->getMockPost());
        $keys = array_keys($post);
        $keys = array_map(function ($a) {
            return '{{post.' . $a . '}}';
        }, $keys);

        return $keys;
    }

    public function getHelpText()
    {
        $tpl = eZTemplate::factory();

        $dummyPost = $this->getMockPost()->jsonSerialize();
        unset($dummyPost['internalId']);
        $tpl->setVariable('post', $dummyPost);
        return $tpl->fetch('design:sensor/webhook_help_text.tpl');
    }

    public function serializeCustomEndpoint($originalEndpoint, $originalPayload, OCWebHook $webHook)
    {
        if (!$originalPayload instanceof Post){
            return $originalEndpoint;
        }

        if ($webHook->hasCustomPayloadParameters()){
            return $this->compileCustomVariable($originalEndpoint, $originalPayload);
        }

        return $originalEndpoint;
    }

    private function getMockPost()
    {
        $dummyPost = new Post();
        $participants = new ParticipantCollection([new Participant()]);
        $dummyPost->expirationInfo = new Post\ExpirationInfo();
        $dummyPost->resolutionInfo = new Post\ResolutionInfo();
        $dummyPost->type = new Post\Type();
        $dummyPost->privacy = new Post\Status\Privacy();
        $dummyPost->moderation = new Post\Status\Moderation();
        $dummyPost->status = new Post\Status();
        $dummyPost->workflowStatus = new Post\WorkflowStatus();
        $dummyPost->participants = $participants;
        $dummyPost->author = new User();
        $dummyPost->reporter = new User();
        $dummyPost->approvers = $participants;
        $dummyPost->owners = $participants;
        $dummyPost->observers = $participants;
        $dummyPost->timelineItems = new TimelineItemCollection([new TimelineItem()]);
        $dummyPost->privateMessages = new PrivateMessageCollection([new PrivateMessage()]);
        $dummyPost->comments = new CommentCollection([new Comment()]);
        $dummyPost->responses = new ResponseCollection([new Response()]);
        $dummyPost->images = [new Post\Field\Image()];
        $dummyPost->attachments = [new Post\Field\Attachment()];
        $dummyPost->categories = [new Post\Field\Category()];
        $dummyPost->geoLocation = new Post\Field\GeoLocation();
        $dummyPost->areas = [new Post\Field\Area()];
        $dummyPost->meta = [];
        $dummyPost->channel = new Post\Channel();
        $dummyPost->images = [new Post\Field\Image()];

        return $dummyPost;
    }

    private function compileCustomPayload(Post $post, array $template)
    {
        $compiled = [];

        foreach ($template as $key => $value){
            $compiledValue = $this->compileCustomVariable($value, $post);
            if ($compiledValue) {
                $compiled[$key] = $compiledValue;
            }
        }

        return $compiled;
    }

    private function compileCustomVariable($value, Post $post)
    {
        if (is_numeric($value)){
            return $value;
        }
        if (is_scalar($value)) {
            if (strpos($value, '{{') !== false) {
                return $this->replacePlaceholder($value, $post);
            }
        }else {
            $compiledValue = [];
            foreach ($value as $key => $subValue) {
                $compiledValue[$key] = $this->compileCustomVariable($subValue, $post);
            }

            return $compiledValue;
        }

        return $value;
    }

    private function replacePlaceholder($value, Post $post)
    {
        $placeholders = $this->getPlaceholders();
        $serialized = $this->postSerializer->serialize($post);
        $postArray = json_decode(json_encode($post), true);

        preg_match_all('/\{{2}(.*?)\}{2}/is', $value,$matches);

        foreach ($matches[0] as $index => $placeholder) {
            if (in_array($placeholder, $placeholders)) {
                $property = str_replace('post.', '', $matches[1][$index]);
                if (isset($serialized[$property])) {
                    $valueToReplace = $serialized[$property];
                    $value = $this->replaceValue($placeholder, $valueToReplace, $value);
                }else{
                    $value = null;
                }
            }else{
                $valueToReplace = $this->api->replacePlaceholders(
                    JmesPath\Env::search($matches[1][$index], $postArray)
                );
                $value = $this->replaceValue($placeholder, $valueToReplace, $value);
            }
        }

        return $this->api->replacePlaceholders($value);
    }

    private function replaceValue($placeholder, $valueToReplace, $value)
    {
        if ($value == $placeholder){
            return $valueToReplace;

        }elseif (is_scalar($valueToReplace)){
            return str_replace($placeholder, $valueToReplace, $value);
        }

        return str_replace($placeholder, json_encode($valueToReplace), $value);
    }
}
