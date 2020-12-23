<?php

class ezfIndexSensorUser extends ezfIndexSensor implements ezfIndexPlugin
{
    public function modify(eZContentObject $contentObject, &$docList)
    {
        $published = $contentObject->attribute('published');

        $month = date('n', $published);
        if ($month >= 10) {
            $quarter = 4;
        } elseif ($month >= 7) {
            $quarter = 3;
        } elseif ($month >= 4) {
            $quarter = 2;
        } else {
            $quarter = 1;
        }

        if ($month >= 6) {
            $semester = 2;
        } else {
            $semester = 1;
        }

        $year = date('Y', $published);
        $values = [];
        $values['creation_day_i'] = date('Yz', $published);
        $values['creation_week_i'] = date('YW', $published);
        $values['creation_month_i'] = date('Ym', $published);
        $values['creation_quarter_i'] = $year . $quarter;
        $values['creation_semester_i'] = $year . $semester;
        $values['creation_year_i'] = $year;

        /** @var eZContentObjectVersion $version */
        $version = $contentObject->currentVersion();
        if ($version !== false) {
            $availableLanguages = $version->translationList(false, false);
            foreach ($availableLanguages as $languageCode) {
                foreach ($values as $key => $value) {
                    $this->addField($docList[$languageCode], $key, $value);
                }
            }
        }
    }
}