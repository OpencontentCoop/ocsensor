<?php

class ezfIndexSensorScenario extends ezfIndexSensor implements ezfIndexPlugin
{
    public function modify(eZContentObject $contentObject, &$docList)
    {
        $dataMap = $contentObject->dataMap();
        $count = 0;
        foreach ($dataMap as $identifier => $attribute){
            if (strpos($identifier, 'criterion_') !== false && $attribute->hasContent()){
                $count++;
            }
        }
        $triggers = [];
        if (isset($dataMap['triggers']) && $dataMap['triggers']->hasContent()) {
            $triggers = explode('|', $dataMap['triggers']->toString());
        }

        /** @var eZContentObjectVersion $version */
        $version = $contentObject->currentVersion();
        if ($version !== false) {
            $availableLanguages = $version->translationList(false, false);
            foreach ($availableLanguages as $languageCode) {
                $this->addField($docList[$languageCode], 'triggers_lk', implode(',', $triggers));
                $this->addField($docList[$languageCode], 'criteria_count_i', $count);
            }
        }
    }
}
