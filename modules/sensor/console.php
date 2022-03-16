<?php
/** @var eZModule $module */

use Opencontent\QueryLanguage\Converter\AnalyzerQueryConverter;
use Opencontent\QueryLanguage\Parser;
use Opencontent\QueryLanguage\Query;
use Opencontent\Sensor\Api\StatisticFactory;
use Opencontent\Sensor\Legacy\SearchService\QueryBuilder;
use Opencontent\Sensor\Legacy\Statistics\SinglePointQueryCapableInterface;

$module = $Params['Module'];
$endpoint = $Params['Endpoint'];
$identifier = $Params['Identifier'];
$http = eZHTTPTool::instance();

if ($endpoint === 'run') {
    $repository = OpenPaSensorRepository::instance();
    $query = $http->variable('q');
    $parameters = (array)$http->variable('parameters');
    $data = $repository->getSearchService()->searchPosts($query, $parameters);
    header('Content-Type: application/json');
    echo json_encode($data);
    eZExecution::cleanExit();
}

if ($endpoint === 'rules') {
    SensorConsole::getRules();
    header('Content-Type: application/json');
    echo json_encode(SensorConsole::getRules());
    eZExecution::cleanExit();
}

if ($endpoint === 'analyze') {
    $repository = OpenPaSensorRepository::instance();
    $query = $http->variable('q');
    $queryBuilder = new QueryBuilder($repository->getPostApiClass());
    $tokenFactory = $queryBuilder->getTokenFactory();
    $parser = new Parser(new Query($query));
    $converter = new AnalyzerQueryConverter();
    $converter->setQuery($parser->setTokenFactory($tokenFactory)->parse());
    $data = $converter->convert();
    header('Content-Type: application/json');
    echo json_encode($data);
    eZExecution::cleanExit();
}


$queries = [];
if ($endpoint === 'stats' && !empty($identifier)) {
    $repository = OpenPaSensorRepository::instance();
    /** @var StatisticFactory $factory */
    $factory = $repository->getStatisticsService()->getStatisticFactoryByIdentifier($identifier);
    if ($factory instanceof SinglePointQueryCapableInterface) {
        $parameters = $http->attribute('get');
        $category = $parameters['_c'];
        $serie = $parameters['_s'];
        $factory->setParameters($parameters);
        $queries[] = $factory->getSinglePointQuery($category, $serie);
    }
}

//echo '<pre>';
//
//foreach ($queries as $queryAndParameters) {
//    $query = $queryAndParameters['query'];
//    $queryBuilder = new QueryBuilder($repository->getPostApiClass());
//    $tokenFactory = $queryBuilder->getTokenFactory();
//    $parser = new Parser(new Query($query));
//    $converter = new AnalyzerQueryConverter();
//    $converter->setQuery($parser->setTokenFactory($tokenFactory)->parse());
//    print_r($queryAndParameters);
//}
//
//
//die();

$tpl = eZTemplate::factory();

$tpl->setVariable('queries', $queries);

$Result = [];
$Result['persistent_variable'] = $tpl->variable('persistent_variable');
$Result['content'] = $tpl->fetch('design:sensor/console.tpl');
$Result['node_id'] = 0;

$contentInfoArray = ['url_alias' => 'sensor/console'];
$contentInfoArray['persistent_variable'] = [];
if ($tpl->variable('persistent_variable') !== false) {
    $contentInfoArray['persistent_variable'] = $tpl->variable('persistent_variable');
}
$Result['content_info'] = $contentInfoArray;
$Result['path'] = [];
