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

class SensorPlaceholderCompiler
{
    private $postSerializer;

    /**
     * @var OpenApi
     */
    protected $api;

    private function __construct()
    {
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

    public static function instance()
    {
        return new SensorPlaceholderCompiler();
    }

    public function getMockPost()
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
        $dummyPost->files = [new Post\Field\File()];
        $dummyPost->attachments = [new Post\Field\Attachment()];
        $dummyPost->categories = [new Post\Field\Category()];
        $dummyPost->geoLocation = new Post\Field\GeoLocation();
        $dummyPost->areas = [new Post\Field\Area()];
        $dummyPost->meta = [];
        $dummyPost->channel = new Post\Channel();

        return $dummyPost;
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

    public function compileValue($value, Post $post)
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
                $compiledValue[$key] = $this->compileValue($subValue, $post);
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