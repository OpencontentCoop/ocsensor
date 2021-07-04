<?php

use League\Event\AbstractListener;
use League\Event\EventInterface;
use Opencontent\Sensor\Api\Values\Event as SensorEvent;
use Opencontent\Sensor\Api\Values\Participant;
use Opencontent\Sensor\Legacy\Repository;

class SensorSocketEmitterListener extends AbstractListener
{
    protected $repository;

    private $secret;

    private $socketUri;

    private $socketPort;

    private $events;

    public function __construct(Repository $repository, $secret = 'abcdefghi', $socketUri = 'http://node', $socketPort = 3000)
    {
        $this->repository = $repository;
        $this->secret = $secret;
        $this->socketUri = $socketUri;
        $this->socketPort = $socketPort;
        $this->events = [
            'on_create',
            'on_read',
            'on_assign',
            'on_group_assign',
            'on_add_comment',
            'on_add_image',
            'on_fix',
            'on_close',
            'on_reopen',
            'on_send_private_message',
            'on_add_comment_to_moderate',
        ];
    }

    public function handle(EventInterface $event, $param = null)
    {
        if ($param instanceof SensorEvent) {
            if (in_array($param->identifier, $this->events)) {
                $this->send([
                    'identifier' => $param->identifier,
                    'data' => [
                        'id' => (int)$param->post->id,
                        'creator' => (int)$param->post->reporter->id,
                        'users' => $param->post->participants->getParticipantIdListByType(Participant::TYPE_USER),
                        'groups' => $param->post->participants->getParticipantIdListByType(Participant::TYPE_GROUP),
                    ]
                ]);
            }
        }
    }

    protected function send($data)
    {
        $requestBody = json_encode($data);
        $signature = base64_encode(hash_hmac('sha256', $requestBody, $this->secret, true));
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Signature: ' . $signature
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->socketUri);
        curl_setopt($ch, CURLOPT_PORT, $this->socketPort);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response){
            $this->repository->getLogger()->error("Invalid response emitting '{$data['identifier']}' to socket on post {$data['data']['id']} with signature {$signature}: " . var_export($response, 1));
        }else {
            $this->repository->getLogger()->info("Emit '{$data['identifier']}' to socket on post {$data['data']['id']} with signature {$signature}: " . var_export($response, 1));
        }

        return $response;
    }
}