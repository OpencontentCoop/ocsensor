<?php


class SensorConnectorConfiguration
{
    private $identifier;

    private $secret;

    private $userId;

    public function __construct(array $data)
    {
        foreach ($data as $key => $value){
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

    public function generateRemoteId($id)
    {
        return $this->identifier . '_' . $id;
    }

    public function isValidPayload($signature, array $payload)
    {
        $payloadJson = json_encode($payload);
        return hash_hmac('sha256', $payloadJson, $this->secret) === $signature;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getUser()
    {
        return eZUser::fetch((int)$this->userId);
    }
}