<?php


use Opencontent\Sensor\Api\Values\Post;

class SensorConnectorConfiguration implements JsonSerializable
{
    private $identifier;

    private $secret;

    private $userName;

    private $type;

    private $endpoint;

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function getType()
    {
        return $this->type;
    }

    public function generateRemoteId($id)
    {
        return $this->identifier . '_' . $id;
    }

    public function isValidPayload($signature, array $payload)
    {
        $payloadJson = json_encode($payload);
        return hash_hmac('sha256', $payloadJson, $this->secret) === $signature;
    }

    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
    }

    public function getRemotePostId(Post $post)
    {
        $object = eZContentObject::fetch($post->id);
        if ($object instanceof eZContentObject) {
            $remoteId = $object->attribute('remote_id');
            list($identifier, $id) = explode('_', $remoteId, 2);
            if ($identifier == $this->identifier) {
                return $id;
            }
        }

        return false;
    }

    public function jsonSerialize()
    {
        return [
            'identifier' => $this->identifier,
            'type' => $this->type,
            'endpoint' => $this->endpoint,
        ];
    }


}