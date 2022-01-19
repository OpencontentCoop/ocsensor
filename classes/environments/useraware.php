<?php

use Opencontent\Opendata\Api\Values\Content;

class SensorUserAwareEnvironmentSettings extends DefaultEnvironmentSettings
{
    use SensorAddMissingLanguageTrait;

    public function filterContent(Content $content)
    {
        $object = eZContentObject::fetch($content->metadata->id);
        $availableLanguages = $content->metadata->languages;
        $content = parent::filterContent($content);
        $content = $this->addMissingLanguage($content);
        $content['metadata']['userAccess'] = $this->getObjectAccess($object, $availableLanguages);

        return $content;
    }

    private function getObjectAccess(eZContentObject $object, $availableLanguages)
    {
        $global = true;
        if (in_array($object->attribute('class_identifier'), [
            'sensor_operator',
            'sensor_area',
            'sensor_scenario',
            'sensor_root',
            'sensor_report',
            'sensor_report_item',
            'user',
            'user_group',
        ])) {
            $global = in_array(eZLocale::currentLocaleCode(), $availableLanguages);
        }
        return [
            'canRead' => $object->canRead(),
            'canEdit' => $object->canEdit() && $global,
            'canRemove' => $object->canRemove() && $global,
//            'canTranslate' => $object->canTranslate(),
        ];
    }
}
