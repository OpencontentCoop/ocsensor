<?php

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\SelectionField;

class SensorPostPrivacyFieldConnector extends SelectionField
{
    private $pageData;

    private $repository;

    public function __construct($attribute, $class, $helper)
    {
        parent::__construct($attribute, $class, $helper);
        $this->pageData = new ObjectHandlerServiceControlSensor();
        $this->repository = OpenPaSensorRepository::instance();
    }

    public function getData()
    {
        $data = parent::getData();
        if (!$this->getHelper()->hasParameter('object') && $this->repository->getSensorSettings()->get('HidePrivacyChoice')) {
            $data = 'No';
        }

        return $data;
    }

    public function getSchema()
    {
        $schema = parent::getSchema();
        if ($this->repository->getSensorSettings()->get('HidePrivacyChoice')){
            $schema['required'] = false;
        }
        return $schema;
    }


    public function getOptions()
    {
        $options = parent::getOptions();
        $options['type'] = 'radio';
        if ($this->repository->getSensorSettings()->get('HidePrivacyChoice')) {
            $options['disabled'] = true;
            $options['hideNone'] = true;
        }
        $options['optionLabels'] = [
            'Si' => $this->repository->isModerationEnabled() ? SensorTranslationHelper::instance()->translate(
                'Everyone will be able to read this report when the %site team approves it', '',
                ['%site' => $this->pageData->logoTitle()]
            ) : SensorTranslationHelper::instance()->translate('Everyone will be able to read this report'),
            'No' => SensorTranslationHelper::instance()->translate(
                'Only the %site team will be able to read this report', '',
                ['%site' => $this->pageData->logoTitle()]
            ),
        ];

        return $options;
    }

}
