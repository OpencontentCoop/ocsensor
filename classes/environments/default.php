<?php

use Opencontent\Opendata\Api\Values\Content;

class SensorDefaultEnvironmentSettings extends DefaultEnvironmentSettings
{
    use SensorAddMissingLanguageTrait;

    public function filterContent( Content $content )
    {
        $contentArray = parent::filterContent($content);
        return $this->addMissingLanguage($contentArray);
    }
}
