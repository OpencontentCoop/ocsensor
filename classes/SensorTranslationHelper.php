<?php

class SensorTranslationHelper
{
    const SITE_DATA_STATIC_PREFIX = 'sensor_translations_';

    const SITE_DATA_CUSTOM_PREFIX = 'sensor_custom_translations_';

    private static $instance;

    private $translationStrings = [];

    private static $translations = [];

    private function __construct()
    {
    }

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new SensorTranslationHelper();
        }
        return self::$instance;
    }

    public function translate($string, $context = false, $replacements = [], $language = null)
    {
        if (!$language){
            $language = eZLocale::currentLocaleCode();
        }
        $translations = $this->loadTranslations($language);
        if ($context && in_array($context, ['privacy', 'status', 'type', 'moderation'])){
            $context = 'sensor/' . $context;
        }else{
            $context = 'sensor';
        }

        if (isset($translations['custom'][$string])) {
            $string = $translations['custom'][$string];
        }elseif (isset($translations[$context][$string])){
            $string = $translations[$context][$string];
        }else{
            eZDebug::writeWarning("Missing translations for $string", '[translations]');
        }

        return $this->insertArguments($string, $replacements);
    }

    protected function insertArguments($text, $arguments)
    {
        if (is_array($arguments)) {
            $replaceList = [];
            foreach ($arguments as $argumentKey => $argumentItem) {
                if (is_int($argumentKey))
                    $replaceList['%' . (($argumentKey % 9) + 1)] = $argumentItem;
                else
                    $replaceList[$argumentKey] = $argumentItem;
            }
            $text = strtr($text, $replaceList);
        }
        return $text;
    }

    public function resetStaticTranslations()
    {
        $translations = $this->getTSTranslations();
        foreach ($translations as $locale => $strings) {
            $this->storeStaticTranslations($locale, $strings);
        }
    }

    private function getTSTranslations()
    {
        $strings = $this->buildTSTranslationStrings();
        $languages = eZContentLanguage::fetchList();
        $translations = [];

        $localeCodes = [];
        foreach ($languages as $language) {
            $localeCode = $language->attribute('locale');
            if ($localeCode === 'ita-PA') continue;
            if ($localeCode === 'eng-GB') $localeCode = 'eng-US'; //workaround override eng-GB
            $localeCodes[] = $localeCode;
        }

        foreach ($localeCodes as $localeCode) {
            unset($GLOBALS['eZTSTranslationTables']);
            unset($GLOBALS['eZTranslatorManagerInstance']);
            foreach ($strings as $context => $sources) {
                eZTSTranslator::initialize($context, $localeCode, 'translation.ts', false);
                $man = eZTranslatorManager::instance();
                foreach ($sources as $source) {
                    $trans = $man->translate($context, $source);
                    $translations[$localeCode][$context][$source] = htmlspecialchars($trans ? $trans : $source);
                }
            }
        }

        if (isset($translations['eng-US'])) {
            $translations['eng-GB'] = $translations['eng-US'];
            unset($translations['eng-US']);
        }

        return $translations;
    }

    private function buildTSTranslationStrings()
    {
        if (empty($this->translationStrings)) {
            $tsXml = simplexml_load_file(eZSys::siteDir() . 'extension/ocsensor/translations/untranslated/translation.ts');
            $this->translationStrings = [];
            foreach ($tsXml as $context) {
                $contextName = (string)$context->name;
                foreach ($context->message as $message) {
                    $this->translationStrings[$contextName][] = (string)$message->source;
                }
            }
        }

        return $this->translationStrings;
    }

    private function storeTranslations($locale)
    {
        $static = $this->loadStaticTranslations($locale);
        $custom = $this->loadCustomTranslations($locale);
        $data = array_merge($static, $custom);
        $cachePath = $this->getTranslationCachePath($locale);
        eZClusterFileHandler::instance($cachePath)->storeContents(json_encode($data));
    }

    public function loadStaticTranslations($locale)
    {
        return $this->loadDbTranslations($locale, self::SITE_DATA_STATIC_PREFIX);
    }

    public function storeStaticTranslations($locale, $data)
    {
        $this->storeDbTranslations($locale, $data, self::SITE_DATA_STATIC_PREFIX);
    }

    public function loadCustomTranslations($locale)
    {
        return $this->loadDbTranslations($locale, self::SITE_DATA_CUSTOM_PREFIX);
    }

    public function storeCustomTranslations($locale, $data)
    {
        $this->storeDbTranslations($locale, $data, self::SITE_DATA_CUSTOM_PREFIX);
    }

    private function loadDbTranslations($locale, $prefix)
    {
        $siteDataName = $prefix . $locale;
        $siteData = eZSiteData::fetchByName($siteDataName);
        if (!$siteData) {
            return [];
        }
        $data = json_decode($siteData->attribute('value'), true);
        return empty($data) ? [] : $data;
    }

    private function storeDbTranslations($locale, $data, $prefix)
    {
        $siteDataName = $prefix . $locale;
        $siteData = eZSiteData::fetchByName($siteDataName);
        if (!$siteData) {
            $siteData = eZSiteData::create($siteDataName, '');
        }
        $siteData->setAttribute('value', json_encode($data));
        $siteData->store();
        $this->storeTranslations($locale);
    }

    private function getTranslationCachePath($locale)
    {
        return eZDir::path([eZSys::cacheDirectory(), 'ocopendata', 'sensor_translations', $locale . '.json']);
    }

    public function loadTranslations($locale)
    {
        if (!isset(self::$translations[$locale])){
            $cachePath = $this->getTranslationCachePath($locale);
            $cacheFile = eZClusterFileHandler::instance($cachePath);
            if ($cacheFile->exists()) {
                $data = $cacheFile->fetchContents();
                self::$translations[$locale] = json_decode($data, true);
            }
        }

        return isset(self::$translations[$locale]) ? self::$translations[$locale] : false;
    }

    public function addCustomTranslation($key, $languages)
    {
        foreach ($languages as $languageCode => $value){
            $data = $this->loadCustomTranslations($languageCode);
            if (empty($data)){
                $data = ['custom' => []];
            }
            $data['custom'][$key] = $value;
            $this->storeCustomTranslations($languageCode, $data);
        }
    }

    public function removeCustomTranslations($keys)
    {
        $languageCodeList = eZContentLanguage::fetchLocaleList();
        foreach ($languageCodeList as $languageCode){
            $data = $this->loadCustomTranslations($languageCode);
            if (!empty($data)){
                foreach ($keys as $key){
                    unset($data['custom'][$key]);
                }
                $this->storeCustomTranslations($languageCode, $data);
            }
        }
    }
}
