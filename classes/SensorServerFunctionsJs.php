<?php

class SensorServerFunctionsJs extends ezjscServerFunctions
{
    public static function translations()
    {
        $locale = eZLocale::currentLocaleCode();
        $data = SensorTranslationHelper::instance()->loadTranslations($locale);
        if ($data) {
            $stringData = json_encode($data);
            return ";SensorI18n = $stringData;";
        }

        return '';
    }
}
