<?php

trait SensorObjectAccessTrait
{
    protected function getObjectAccess(eZContentObject $object, $availableLanguages)
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