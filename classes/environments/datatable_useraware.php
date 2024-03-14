<?php

use Opencontent\Opendata\Api\Values\Content;

class SensorDatatableEnvironmentSettings extends DatatableEnvironmentSettings
{
    use SensorAddMissingLanguageTrait;
    use SensorObjectAccessTrait;

    protected function filterMetaData( Content $content )
    {
        $availableLanguages = $content->metadata->languages;
        $object = eZContentObject::fetch($content->metadata->id);
        $content = parent::filterMetaData($content);
        if ($object instanceof eZContentObject) {
            $content->metadata['userAccess'] = $this->getObjectAccess($object, $availableLanguages);
            $content->metadata['isEnabled'] = true;
            if ($user = eZUser::fetch((int)$object->attribute('id'))) {
                $content->metadata['isEnabled'] = $user->isEnabled();
            }
        }
        return $content;
    }
}
