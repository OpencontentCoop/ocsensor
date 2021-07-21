<?php

class SensorReport
{
    public static function getItemData(eZContentObject $item)
    {
        $data = [];
        if ($item->attribute('class_identifier') == 'sensor_report_item') {
            $itemDataMap = $item->dataMap();
            if (isset($itemDataMap['data'])) {
                if ($itemDataMap['data']->hasContent()) {
                    $data = json_decode($itemDataMap['data']->attribute('data_text'), true);
                } else {
                    $data = self::generateItemData($item);
                }
            }
        }

        return $data;
    }

    public static function generateItemData(eZContentObject $item, $store = false)
    {
        $data = [];
        if ($item->attribute('class_identifier') == 'sensor_report_item') {
            $itemDataMap = $item->dataMap();
            if (isset($itemDataMap['link']) && $itemDataMap['link']->hasContent()) {
                $link = trim($itemDataMap['link']->toString());
                $urlParts = parse_url($link);
                $statIdentifier = isset($urlParts['path']) ? basename($urlParts['path']) : false;
                $parameters = [];
                if (isset($urlParts['query'])) {
                    parse_str($urlParts['query'], $parameters);
                }
                if ($statIdentifier) {
                    try {
                        $stats = OpenPaSensorRepository::instance()->getStatisticsService()->getStatisticFactories(true);
                        foreach ($stats as $stat) {
                            if ($stat->getIdentifier() == $statIdentifier) {
                                $stat->setParameters($parameters);
                                $format = isset($parameters['format']) ? $parameters['format'] : 'data';
                                $data = $stat->getDataByFormat($format);
                            }
                        }
                    } catch (Exception $e) {
                        eZDebug::writeError($e->getMessage(), __METHOD__);
                    }
                }
            }
            if (isset($itemDataMap['data']) && $store) {
                $itemDataMap['data']->setAttribute('data_text', json_encode($data));
                $itemDataMap['data']->store();
            }
        }

        return $data;
    }
}