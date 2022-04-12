<?php

trait StatsPivotRepository
{
    abstract protected function buildQuery(OCCustomSearchParameters $parameters, $guid = null);

    public function find(OCCustomSearchParameters $parameters)
    {
        eZDebug::createAccumulator('Query build', 'eZ Find');
        eZDebug::accumulatorStart('Query build');
        $queryParams = $this->buildQuery($parameters);
        eZDebug::accumulatorStop('Query build');

        if ($parameters instanceof SensorDailySearchParameters){
            $queryParams = $this->addStatsToQueryParams($parameters->getStats(), $queryParams);
            $queryParams = $this->addPivotToQueryParams($parameters->getPivot(), $queryParams);
        }

        eZDebug::createAccumulator('Engine time', 'eZ Find');
        eZDebug::accumulatorStart('Engine time');
        $solr = new eZSolrBase();
        $resultArray = $solr->rawSearch($queryParams);
        eZDebug::accumulatorStop('Engine time');

        $result = new StatsPivotSearchResult($this);

        return $result->fromArrayResult($resultArray);
    }

    protected function addPivotToQueryParams($filterParams, $queryParams)
    {
        if (isset($filterParams['facet'])){
            $queryParams['facet.pivot'] = is_array($filterParams['facet']) ? implode(',', $filterParams['facet']) : $filterParams['facet'];

            if (isset($filterParams['mincount'])){
                $queryParams['facet.pivot.mincount'] = (int)$filterParams['mincount'];
            }
        }

        return $queryParams;
    }

    protected function addStatsToQueryParams($filterParams, $queryParams)
    {
        if (isset($filterParams['field'])) {
            $fields = $filterParams['field'];
            if (!is_array($fields)) {
                $fields = [$fields];
            }

            foreach ($fields as $field) {
                $fieldName = eZSolr::getFieldName($field);
                $queryParams['stats'] = 'true';
                $queryParams['stats.field'][] = $fieldName;

                if (isset($filterParams['facet'])) {
                    $facetsParams = $filterParams['facet'];
                    if (!is_array($facetsParams)) {
                        $facetsParams = array($facetsParams);
                    }
                    $facetNameList = array();
                    foreach ($facetsParams as $facetParam) {
                        $facetName = eZSolr::getFieldName($facetParam, false, 'facet');
                        $facetNameList[] = $facetName;
                    }
                    $queryParams['stats.facet'] = $facetNameList;
                }
            }
        }

        return $queryParams;
    }
}