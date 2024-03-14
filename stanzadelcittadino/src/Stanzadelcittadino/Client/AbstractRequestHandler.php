<?php

namespace Opencontent\Stanzadelcittadino\Client;

abstract class AbstractRequestHandler implements RequestHandlerInterface
{
    protected $method = 'GET';

    protected $path = '/';

    public function getRequestMethod(): string
    {
        return $this->method;
    }

    public function getRequestPath(): string
    {
        return $this->path;
    }

    public function getMinimumCredential(): ?string
    {
        return null;
    }

    public function getRequestOptions(): array
    {
        return [];
    }

    public function handleError(\Throwable $e)
    {
    }

    public function handleResponse(string $response): ?array
    {
        return null;
    }

    public function __toString()
    {
        return static::class;
    }
}