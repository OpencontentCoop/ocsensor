<?php

trait SensorAddMissingLanguageTrait
{
    protected function addMissingLanguage($contentArray)
    {
        $contentLanguages = [];
        foreach (eZContentLanguage::fetchList() as $language) {
            $contentLanguages[] = $language->attribute('locale');
        }

        $defaultValues = $defaultLocale = false;
        foreach ($contentArray['data'] as $locale => $values){
            $defaultValues = $values;
            $defaultLocale = $locale;
            break;
        }
        if ($defaultValues) {
            foreach ($contentLanguages as $locale) {
                if (!isset($contentArray['data'][$locale])) {
                    $contentArray['data'][$locale] = $defaultValues;
                    $contentArray['metadata']['name'][$locale] = $contentArray['metadata']['name'][$defaultLocale];
                }
            }
        }

        return $contentArray;
    }
}
