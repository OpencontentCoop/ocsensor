<?php

use Opencontent\Sensor\Api\Exception\InvalidInputException;

class BehalfOrganizationConnector extends BehalfUserConnector
{
    protected function load()
    {
        if (!self::$isLoaded) {
            if (OpenPaSensorRepository::instance()->getCurrentUser()->behalfOfMode !== true) {
                throw new InvalidInputException("The current user can not post on behalf of others");
            }
            $this->language = \eZLocale::currentLocaleCode();
            $this->getHelper()->setSetting('language', $this->language);
            $this->classConnector = new BehalfUserAsOrganizationClassConnector(eZContentClass::fetchByIdentifier('user'), $this->getHelper());
            self::$isLoaded = true;
        }
    }
}