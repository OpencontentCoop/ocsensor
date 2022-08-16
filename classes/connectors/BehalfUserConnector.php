<?php

use Opencontent\Ocopendata\Forms\Connectors\AbstractBaseConnector;
use Opencontent\Sensor\Api\Exception\InvalidInputException;

class BehalfUserConnector extends AbstractBaseConnector
{
    protected static $isLoaded;
    protected $language;
    /**
     * @var BehalfUserClassConnector
     */
    protected $classConnector;

    public function runService($serviceIdentifier)
    {
        $this->load();
        return parent::runService($serviceIdentifier);
    }

    protected function load()
    {
        if (!self::$isLoaded) {
            if (OpenPaSensorRepository::instance()->getCurrentUser()->behalfOfMode !== true) {
                throw new InvalidInputException("The current user can not post on behalf of others");
            }
            $this->language = \eZLocale::currentLocaleCode();
            $this->getHelper()->setSetting('language', $this->language);
            $this->classConnector = new BehalfUserClassConnector(eZContentClass::fetchByIdentifier('user'), $this->getHelper());
            self::$isLoaded = true;
        }
    }

    protected function getData()
    {
        return $this->classConnector->getData();
    }

    protected function getSchema()
    {
        return $this->classConnector->getSchema();
    }

    protected function getOptions()
    {
        return array_merge_recursive(
            array(
                "form" => array(
                    "attributes" => array(
                        "class" => 'opendata-connector',
                        "action" => $this->getHelper()->getServiceUrl('action', $this->getHelper()->getParameters()),
                        "method" => "post",
                        "enctype" => "multipart/form-data"
                    )
                ),
            ),
            $this->classConnector->getOptions()
        );
    }

    protected function getView()
    {
        return $this->classConnector->getView();
    }

    protected function submit()
    {
        $this->classConnector->setSubmitData($_POST);
        return $this->classConnector->submit();
    }

    protected function upload()
    {
        return $this->classConnector->upload();
    }

}