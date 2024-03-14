<?php

namespace Opencontent\Stanzadelcittadino\Client;

use GuzzleHttp\Client;
use Throwable;

class Utils
{
    public static function getFirstNotEmpty($haystack, ...$needles): string
    {
        foreach ($needles as $needle) {
            if (!empty($haystack[$needle])) {
                return $haystack[$needle];
            }
        }

        return '';
    }

    /**
     * @param string $query
     * @return array
     */
    public static function getNominatimLocation(string $query): array
    {
        try {
            $response = (string)(new Client())->request(
                'GET',
                'https://nominatim.openstreetmap.org/search',
                [
                    'query' => [
                        'q' => $query,
                        'countrycodes' => 'it',
                        'viewbox' => '8.66272,44.52197,9.09805,44.37590',
                        'bounded' => 1,
                        'format' => 'jsonv2',
                        'addressdetails' => 1,
                        'limit' => 1,
                    ],
                ]
            )->getBody();
            $response = json_decode($response, true);
            if (isset($response[0])) {
                return $response[0];
            }
        } catch (Throwable) {
        }

        return [];
    }
}