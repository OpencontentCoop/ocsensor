<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Response;

class SensorCategoryPredictor
{
    private static $instance;

    private $endpoint;

    private $isEnabled;

    private $requestTimeout = 60;

    private $verifySsl = true;

    private function __construct()
    {
        $sensorIni = eZINI::instance('ocsensor.ini');
        $this->isEnabled = $sensorIni->variable('CategoryPredictor', 'UsePredictor') == 'enabled';
        $this->endpoint = $sensorIni->variable('CategoryPredictor', 'Endpoint');
    }

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new SensorCategoryPredictor();
        }

        return self::$instance;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function predict($postId, $subject, $description)
    {
        if (!$this->isEnabled) {
            return [];
        }

        $requestBody = [
            'subject' => $subject,
            'description' => $description,
        ];
        $headers = [];
        $client = new Client();
        $promises = [
            'micro' => $client->requestAsync(
                'POST',
                $this->endpoint . '/categoryhints/micro/',
                [
                    'timeout' => $this->requestTimeout,
                    'verify' => $this->verifySsl,
                    'headers' => $headers,
                    'json' => $requestBody,
                ]
            ),
            'macro' => $client->requestAsync(
                'POST',
                $this->endpoint . '/categoryhints/macro/',
                [
                    'timeout' => $this->requestTimeout,
                    'verify' => $this->verifySsl,
                    'headers' => $headers,
                    'json' => $requestBody,
                ]
            ),
        ];

        $results = Promise\settle($promises)->wait();

        $data = [];
        $simpleTree = [];
        $predictions = [
            'macro' => [],
            'micro' => [],
        ];
        $repository = OpenPaSensorRepository::instance();
        $categoryTree = $repository->getCategoriesTree();
        foreach ($categoryTree->attribute('children') as $category) {
            $simpleTree[$category->attribute('id')] = [
                'id' => $category->attribute('id'),
                'name' => $category->attribute('name'),
                'score' => 0,
                'children' => [],
            ];
            foreach ($category->attribute('children') as $subCategory) {
                $simpleTree[$category->attribute('id')]['children'][] = [
                    'id' => $subCategory->attribute('id'),
                    'name' => $subCategory->attribute('name'),
                    'score' => 0,
//                    'children' => []
                ];
            }
        }

        foreach ($results as $id => $result) {
            if ($result['state'] == Promise\PromiseInterface::FULFILLED) {
                /** @var Response $response */
                $response = $result['value'];
                $predictions[$id] = json_decode((string)$response->getBody(), true)['pred_cats'];
            } else {
                /** @var RequestException $reason */
                $reason = $result['reason'];
                if ($reason->hasResponse()) {
                    $error = (string)$reason->getResponse()->getBody();
                } else {
                    $error = $reason->getMessage();
                }
                $repository->getLogger()->error($error, ['post' => $postId]);
            }
        }

        foreach ($predictions['macro'] as $prediction) {
            if (isset($simpleTree[$prediction['parentCategoryId']])) {
                $category = $simpleTree[$prediction['parentCategoryId']];
                $category['score'] = (float)number_format($prediction['probability']*100, 2);
                $category['children'] = [];
                $data[$category['id']] = $category;
            }
        }
        foreach ($predictions['micro'] as $prediction) {
            foreach ($simpleTree as $parent){
                foreach ($parent['children'] as $child){
                    if ($child['id'] == $prediction['categoryId']){
                        $child['score'] = (float)number_format($prediction['probability']*100, 2);
                        if (!isset($data[$parent['id']])){
                            $parent['children'] = [];
                            $data[$parent['id']] = $parent;
                        }
                        $data[$parent['id']]['children'][] = $child;
                    }
                }
            }
        }

        return array_values($data);
    }
}
