<?php

use Opencontent\QueryLanguage\Converter\AnalyzerQueryConverter;
use Opencontent\QueryLanguage\Parser;
use Opencontent\QueryLanguage\Query;
use Opencontent\Sensor\Legacy\SearchService\QueryBuilder;
use Opencontent\Sensor\Legacy\SearchService\SolrMapper;

class SensorConsole
{
    public static function getRules()
    {
        $repository = OpenPaSensorRepository::instance();
        $queryBuilder = new QueryBuilder($repository->getPostApiClass());
        $tokenFactory = $queryBuilder->getTokenFactory();

        $filters = [];
        foreach (SolrMapper::getMap() as $field => $filter){
            if (strpos($field, '*') === false) {
                $filters[] = [
                    'id' => $filter,
                    'label' => $field,
                    'type' => 'string'
                ];
            }
        }

        return $filters;
    }
}
