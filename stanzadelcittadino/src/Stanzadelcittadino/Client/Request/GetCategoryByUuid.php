<?php

namespace Opencontent\Stanzadelcittadino\Client\Request;

use Opencontent\Stanzadelcittadino\Client\Exceptions\CategoryByIdNotFound;
use Psr\Http\Client\ClientExceptionInterface;

class GetCategoryByUuid extends GetCategories
{
    /**
     * @var string
     */
    private $uuid;

    public function __construct(string $uuid)
    {
        $this->uuid = $uuid;
    }

    public function getRequestPath(): string
    {
        return parent::getRequestPath() . '/' . $this->uuid; // TODO: Change the autogenerated stub
    }

    public function handleError(\Throwable $e)
    {
        if ($e instanceof ClientExceptionInterface){
            throw new CategoryByIdNotFound();
        }
    }
}