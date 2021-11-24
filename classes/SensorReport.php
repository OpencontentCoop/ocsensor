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
                                $stat->init()->setParameters($parameters);
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
                if (isset($itemDataMap['images'])) {
                    $images = [];
                    $imageUrlList = self::generateItemImages($data);
                    foreach ($imageUrlList as $url) {
                        $binary = eZHTTPTool::getDataByURL($url);
                        if ($binary) {
                            $filename = basename($url);
                            $dir = sys_get_temp_dir();
                            eZFile::create($filename, $dir, $binary);
                            $images[] = $dir . '/' . $filename;
                        }
                    }
                    if (!empty($images)) {
                        $itemDataMap['images']->fromString(implode('|', $images));
                        $itemDataMap['images']->store();
                        foreach ($images as $image){
                            @unlink($image);
                        }
                    }
                }

                $itemDataMap['data']->setAttribute('data_text', json_encode($data));
                $itemDataMap['data']->store();
            }
        }

        return $data;
    }

    public static function generateItemImages(array $data)
    {
        $images = [];
        foreach ($data as $item){
            if (
                isset($item['type'])
                && in_array($item['type'], ['highcharts', 'stockChart'])
                && eZINI::instance('ocsensor.ini')->hasVariable('HighchartsExport', 'Server')
                && eZINI::instance('ocsensor.ini')->variable('HighchartsExport', 'Server') == 'enabled'
            ){
                $chartConfig = $item['config'];
                $chartConfig['exporting'] = [
                    'sourceWidth' => 1500,
                    'sourceHeight' => 800,
                ];
                if (isset($chartConfig['title']['text'])){
                    $chartConfig['title']['text'] = '';
                }
                $postData = json_encode([
                    'async' => true,
                    'infile' => $chartConfig,
                    'type' => 'png',
                    'constr' => $item['type'] == 'highcharts' ? 'Chart' : 'StockChart'
                ]);
                $url = eZINI::instance('ocsensor.ini')->variable('HighchartsExport', 'Uri');
                $headers = [];
                $headers[] = 'Content-Type: application/json';
                $headers[] = 'Content-Length: ' . strlen($postData);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                curl_setopt($ch, CURLOPT_HEADER, 1);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
                curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                curl_setopt($ch, CURLINFO_HEADER_OUT, true);

                $response = curl_exec($ch);
                if ($response) {
                    $info = curl_getinfo($ch);
                    $responseData = substr($response, $info['header_size']);
                    if (strpos($responseData, 'error') !== false){
                        eZDebug::writeError($responseData, __METHOD__);
                    }else {
                        $link = $url . '/' . $responseData;
                        $images[] = $link;
                    }
                }else{
                    $errorMessage = curl_error($ch);
                    eZDebug::writeError($errorMessage, __METHOD__);
                }
            }
        }

        return $images;
    }
}
