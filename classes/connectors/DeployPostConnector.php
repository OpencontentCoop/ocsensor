<?php

use Opencontent\Ocopendata\Forms\Connectors\AbstractBaseConnector;
use Opencontent\Sensor\Api\Action\Action;
use Opencontent\Sensor\Api\Values\ParticipantRole;
use Opencontent\Sensor\Legacy\PermissionService;

class DeployPostConnector extends AbstractBaseConnector
{
    protected $isLoaded;

    /**
     * @var eZContentObject
     */
    protected $post;

    /**
     * @var eZContentObjectAttribute[]
     */
    protected $postDataMap;

    /**
     * @var OpenPaSensorRepository
     */
    protected $repository;

    /**
     * @var eZContentClassAttribute
     */
    protected $startDateAttribute;

    /**
     * @var eZContentClassAttribute
     */
    protected $endDateAttribute;

    /**
     * @var eZContentClassAttribute
     */
    protected $documentNumberAttribute;

    /**
     * @var boolean
     */
    protected $isAlreadyDeployed;

    protected function load()
    {
        if (!$this->isLoaded) {
            $this->repository = OpenPaSensorRepository::instance();
            $this->startDateAttribute = $this->repository->getPostContentClassAttribute('start_date');
            $this->endDateAttribute = $this->repository->getPostContentClassAttribute('end_date');
            $this->documentNumberAttribute = $this->repository->getPostContentClassAttribute('document_number');
            if ($this->hasParameter('post')) {
                $this->post = eZContentObject::fetch((int)$this->getParameter('post'));
                if (!$this->post instanceof eZContentObject) {
                    throw new Exception('Not found');
                }
                $this->postDataMap = $this->post->dataMap();
                $this->isAlreadyDeployed = in_array('sensor/deployed', $this->post->stateIdentifierArray());
            }
            $this->isLoaded = true;
        }
    }

    public function runService($serviceIdentifier)
    {
        $this->load();
        return parent::runService($serviceIdentifier);
    }

    protected function getData()
    {
        return [
            'start_date' => $this->startDateAttribute && $this->postDataMap['start_date']->hasContent()?
                date('d/m/Y', $this->postDataMap['start_date']->toString()) : null,
            'end_date' => $this->endDateAttribute && $this->postDataMap['end_date']->hasContent() ?
                date('d/m/Y', $this->postDataMap['end_date']->toString()) : null,
            'document_number' => $this->documentNumberAttribute ?
                $this->postDataMap['document_number']->toString() : null,
        ];
    }

    protected function getSchema()
    {
        return [
            "title" => SensorTranslationHelper::instance()->translate('Impostazioni patto'),
            "type" => "object",
            "properties" => [
                "start_date" => [
                    "type" => "string",
                    "title" => $this->startDateAttribute ? $this->startDateAttribute->attribute(
                        'name'
                    ) : 'Inizio validità',
                    "format" => "date",
                    'required' => true,
                ],
                "end_date" => [
                    "type" => "string",
                    "title" => $this->endDateAttribute ? $this->endDateAttribute->attribute('name') : 'Fine validità',
                    "format" => "date",
                    'required' => true,
                ],
                "document_number" => [
                    "type" => "string",
                    "title" => $this->documentNumberAttribute ?
                        $this->documentNumberAttribute->attribute('name') : 'Numero di determina',
                    'required' => true,
                ],
            ],
        ];
    }

    protected function getOptions()
    {
        return [
            "form" => [
                "attributes" => [
                    "action" => $this->getHelper()->getServiceUrl('action', $this->getHelper()->getParameters()),
                    "method" => "post",
                ],
                "buttons" => [
                    "submit" => [],
                ],
            ],
            "fields" => [
                "start_date" => [
                    'type' => 'date',
                    "dateFormat" => "DD/MM/YYYY",
                    "picker" => [
                        "format" => "DD/MM/YYYY",
                        "useCurrent" => true,
                        "locale" => "it",
                    ],
                    "locale" => "it",
                    "helper" => $this->startDateAttribute ? $this->startDateAttribute->attribute('description') : '',
                ],
                "end_date" => [
                    'type' => 'date',
                    "dateFormat" => "DD/MM/YYYY",
                    "picker" => [
                        "format" => "DD/MM/YYYY",
                        "useCurrent" => false,
                        "locale" => "it",
                    ],
                    "locale" => "it",
                    "helper" => $this->endDateAttribute ? $this->endDateAttribute->attribute('description') : '',
                ],
                "document_number" => [
                    'readonly' => $this->isAlreadyDeployed,
                ],
            ],
        ];
    }

    protected function getView()
    {
        return [
            "parent" => "bootstrap-edit",
            "locale" => "it_IT",
        ];
    }

    protected function submit()
    {
        $post = $this->repository->getPostService()->loadPost((int)$this->post->attribute('id'));
        if (
            !$post->participants->getParticipantsByRole(ParticipantRole::ROLE_APPROVER)->getUserById($this->repository->getCurrentUser()->id)
            && !PermissionService::isSuperAdmin($this->repository->getCurrentUser())
        ){
            throw new Exception('Forbidden');
        }

        if ($this->startDateAttribute && $this->endDateAttribute && $this->documentNumberAttribute) {
            $attributes = [];
            $startDateTime = \DateTime::createFromFormat('d/m/Y', $_POST['start_date']);
            if (!$startDateTime instanceof \DateTime){
                throw new Exception('Data non valida ' . $_POST['start_date']);
            }
            $attributes['start_date'] = $startDateTime->format('U');

            $endDateTime = \DateTime::createFromFormat('d/m/Y', $_POST['end_date']);
            if (!$endDateTime instanceof \DateTime){
                throw new Exception('Data non valida ' . $_POST['end_date']);
            }
            $attributes['end_date'] = $endDateTime->format('U');

            if (!$this->isAlreadyDeployed) {
                $attributes['document_number'] = $_POST['document_number'];
            }

            if (!empty($attributes['start_date']) && !empty($attributes['end_date'])
                && (!empty($attributes['document_number']) || $this->isAlreadyDeployed)) {

                eZContentFunctions::updateAndPublishObject($this->post, ['attributes' => $attributes]);

                $this->repository->getActionService()->runAction(
                    new Action('close', ['label' => 'sensor.deployed']),
                    $this->repository->getPostService()->loadPost((int)$post->attribute('id'))
                );

                return true;
            }
        }
        throw new Exception('Error deploying post');
    }

    protected function upload()
    {
        return false;
    }

}
