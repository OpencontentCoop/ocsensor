<?php

namespace Opencontent\Stanzadelcittadino\Client\Request;

use Opencontent\Stanzadelcittadino\Client\Exceptions\MessageNotFound;
use Psr\Http\Client\ClientExceptionInterface;

class GetApplicationMessageByText extends GetApplicationMessages
{
    private $text;

    public function __construct(string $text, string $uuid, int $version = 1)
    {
        $this->text = $text;
        parent::__construct($uuid, $version);
    }

    public function handleResponse(string $response): ?array
    {
        $messages = json_decode($response, true);
        foreach ($messages as $message) {
            if ($message['message'] === $this->text) {
                return $message;
            }
        }

        throw new MessageNotFound();
    }
}