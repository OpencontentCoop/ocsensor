<?php

namespace Opencontent\Stanzadelcittadino\Client;

interface RequestHandlerInterface
{
    public function getRequestMethod(): string;

    public function getRequestPath(): string;

    public function getMinimumCredential(): ?string;

    public function getRequestOptions(): array;

    /**
     * @param \Throwable $e
     * @return array|void
     */
    public function handleError(\Throwable $e);

    public function handleResponse(string $response): ?array;

    public function __toString();
}