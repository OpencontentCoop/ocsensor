<?php

use Opencontent\Ocopendata\Forms\Connectors\AbstractBaseConnector;
use Opencontent\Sensor\Legacy\Utils\TreeNode;

class OperatorSettingsConnector extends AbstractBaseConnector
{
    private static $isLoaded;

    private $language;

    /**
     * @var eZUser
     */
    private $user;

    /**
     * @var SocialUser
     */
    private $socialUser;

    /**
     * @var OpenPaSensorRepository
     */
    private $repository;

    public function runService($serviceIdentifier)
    {
        $this->load();
        return parent::runService($serviceIdentifier);
    }

    private function load()
    {
        if (!self::$isLoaded) {
            $this->language = \eZLocale::currentLocaleCode();
            $this->getHelper()->setSetting('language', $this->language);
            $userId = (int)$this->getHelper()->getParameter('user');
            $this->user = eZUser::fetch($userId);
            if (!$this->user instanceof eZUser) {
                throw new Exception('User not found');
            }
            $this->repository = OpenPaSensorRepository::instance();
            if ($this->user->contentObject()->attribute('class_identifier') != $this->repository->getOperatorContentClass()->attribute('identifier')) {
                throw new Exception('User is not an operator');
            }
            $this->socialUser = SocialUser::instance($this->user);
            self::$isLoaded = true;
        }
    }

    protected function getData()
    {
        return [
            'block_mode' => $this->socialUser->hasBlockMode(),
            'sensor_deny_comment' => $this->socialUser->hasDenyCommentMode(),
            'sensor_can_behalf_of' => $this->socialUser->hasCanBehalfOfMode(),
            'moderate' => $this->socialUser->hasModerationMode(),
        ];
    }

    protected function getSchema()
    {
        return [
            "title" => $this->user->contentObject()->attribute('name'),
            "type" => "object",
            'properties' => [
                'block_mode' => ['type' => 'boolean'],
                'sensor_deny_comment' => ['type' => 'boolean'],
                'sensor_can_behalf_of' => ['type' => 'boolean'],
                'moderate' => ['type' => 'boolean'],
            ]
        ];
    }

    protected function getOptions()
    {
        return [
            "form" => [
                "attributes" => [
                    "class" => 'opendata-connector',
                    "action" => $this->getHelper()->getServiceUrl('action', $this->getHelper()->getParameters()),
                    "method" => "post",
                    "enctype" => "multipart/form-data"
                ]
            ],
            'helper' => 'Username: ' . $this->user->Login . ' &middot; Email: ' . $this->user->Email,
            'hideInitValidationError' => true,
            'fields' => [
                'block_mode' => ['type' => 'checkbox', 'rightLabel' => 'Blocca utente'],
                'sensor_deny_comment' => ['type' => 'checkbox', 'rightLabel' => 'Impedisci all\'utente di commentare'],
                'sensor_can_behalf_of' => ['type' => 'checkbox', 'rightLabel' => 'Permetti all\'utente di inserire segnalazioni per conto di altri'],
                'moderate' => ['type' => 'checkbox', 'rightLabel' => 'Modera sempre le attività dell\'utente'],
            ]
        ];
    }

    protected function getView()
    {
        return [
            'parent' => 'bootstrap-edit',
            'locale' => 'it_IT'
        ];
    }

    protected function submit()
    {
        $data = $_POST;
        $blockMode = $data['block_mode'] === 'true';
        $denyComment = $data['sensor_deny_comment'] === 'true';
        $cabBehalf = $data['sensor_can_behalf_of'] === 'true';
        $moderate = $data['moderate'] === 'true';

        $changeEnabling = $blockMode !== $this->socialUser->hasBlockMode();
        $this->socialUser->setBlockMode($blockMode);
        $this->socialUser->setDenyCommentMode($denyComment);
        $this->socialUser->setCanBehalfOfMode($cabBehalf);
        $this->socialUser->setModerationMode($moderate);

        if ($changeEnabling) {
            TreeNode::clearCache($this->repository->getOperatorsRootNode()->attribute('node_id'));
            TreeNode::clearCache($this->repository->getGroupsRootNode()->attribute('node_id'));
        }

        $userSettings = [
            'block_mode' => $this->socialUser->hasBlockMode(),
            'sensor_deny_comment' => $this->socialUser->hasDenyCommentMode(),
            'sensor_can_behalf_of' => $this->socialUser->hasCanBehalfOfMode(),
            'moderate' => $this->socialUser->hasModerationMode(),
        ];

        $this->repository->getLogger()->info("Modified info for user {$this->user->Login} #" . $this->user->id(), $userSettings);

        return [
            'message' => 'success',
            'method' => 'update',
            'content' => $userSettings
        ];
    }

    protected function upload()
    {
        throw new InvalidArgumentException('Upload not handled');
    }

}
