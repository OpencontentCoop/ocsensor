<?php

use Opencontent\Opendata\Api\Values\Content;

class SensorUserAwareEnvironmentSettings extends DefaultEnvironmentSettings
{
    use SensorAddMissingLanguageTrait;
    use SensorObjectAccessTrait;

    public function filterContent(Content $content)
    {
        $object = eZContentObject::fetch($content->metadata->id);
        $availableLanguages = $content->metadata->languages;
        $content = parent::filterContent($content);
        $content = $this->addMissingLanguage($content);
        $content['metadata']['userAccess'] = $this->getObjectAccess($object, $availableLanguages);

        return $content;
    }
}
