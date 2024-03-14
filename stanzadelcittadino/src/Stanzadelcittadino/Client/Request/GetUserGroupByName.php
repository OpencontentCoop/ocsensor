<?php

namespace Opencontent\Stanzadelcittadino\Client\Request;

use Opencontent\Stanzadelcittadino\Client\Exceptions\UserGroupByNameNotFound;

class GetUserGroupByName extends GetUserGroups
{

    /**
     * @var string
     */
    private $groupName;

    /**
     * @var bool
     */
    private $caseSensitive;

    public function __construct(string $groupName, bool $caseSensitive = false)
    {
        $this->groupName = $groupName;
        $this->caseSensitive = $caseSensitive;
    }

    public function handleResponse(string $response): ?array
    {
        $userGroups = json_decode($response, true);
        foreach ($userGroups as $userGroup) {
            if ($this->caseSensitive) {
                $isSame = trim($userGroup['name']) === trim($this->groupName);
            } else {
                $isSame = strtolower(trim($userGroup['name'])) === strtolower(trim($this->groupName));
            }
            if ($isSame) {
                return $userGroup;
            }
        }

        throw new UserGroupByNameNotFound();
    }

}