<?php

class SensorCharts
{
    protected $parameters;

    /**
     * @var OpenPaSensorRepository
     */
    protected $repository;

    /**
     * @var \OpenContent\Sensor\Legacy\SearchService
     */
    protected $searchService;

    protected $requestFilters = array();

    protected static $availableCharts = array(
        array(
            'identifier' => 'status',
            'name' => 'Stato',
            'template_uri' => 'design:sensor/charts/status.tpl',
            'call_method' => 'statusData'
        ),        
        array(
            'identifier' => 'categories',
            'name' => 'Aree tematiche',
            'template_uri' => 'design:sensor/charts/categories.tpl',
            'call_method' => 'categoriesData'
        ),
        array(
            'identifier' => 'areas',
            'name' => 'Punti sulla mappa',
            'template_uri' => 'design:sensor/charts/areas.tpl',
            'call_method' => 'areasData'
        ),
        array(
            'identifier' => 'type',
            'name' => 'Tipologia di segnalazione',
            'template_uri' => 'design:sensor/charts/type.tpl',
            'call_method' => 'typeData'
        ),
        array(
            'identifier' => 'times',
            'name' => 'Tempi di esecuzione',
            'template_uri' => 'design:sensor/charts/times.tpl',
            'call_method' => 'timesData'
        ),
        array(
            'identifier' => 'timesAvg',
            'name' => 'Media mensile tempi di esecuzione',
            'template_uri' => 'design:sensor/charts/times_avg.tpl',
            'call_method' => 'timesAvgData'
        ),
        array(
            'identifier' => 'performance',
            'name' => 'Tempi di risposta e di chiusura',
            'template_uri' => 'design:sensor/charts/performance.tpl',
            'call_method' => 'performanceData'
        )        
    );

    public static function listAvailableCharts()
    {
        return self::$availableCharts;
    }

    public static function fetchChartByIdentifier( $identifier )
    {
        foreach ( self::$availableCharts as $chart )
        {
            if ( $chart['identifier'] == $identifier )
            {
                return $chart;
            }
        }

        return false;
    }

    public function __construct( $chartParameters = array() )
    {
        $this->parameters = $chartParameters;
        $this->repository = OpenPaSensorRepository::instance();
        $this->searchService = $this->repository->getSearchService();
        if ( isset( $this->parameters['filters'] ) )
        {
            $filters = array();
            foreach( $this->parameters['filters'] as $filter )
            {
                $name = $filter['name'];
                $value = $filter['value'];
                if ( !isset( $this->requestFilters[$name] ) )
                    $this->requestFilters[$name] = array();
                $this->requestFilters[$name][] = $value;
            }
        }
    }

    public function getData()
    {
        $data = array();
        if ( isset( $this->parameters['type'] ) )
        {
            $chart = self::fetchChartByIdentifier( $this->parameters['type'] );

            if ( method_exists( $this, $chart['call_method'] ) )
            {
                $functionName = $chart['call_method'];
                $data = $this->$functionName();
            }
            elseif ( is_callable( $chart['call_method'] ) )
            {
                $data = call_user_func( $chart['call_method'] ); //@todo make factory
            }
        }

        return $data;
    }

    protected function getMonthlyFacets( $facets )
    {
        $startResult = $this->searchService->query(
            $this->searchService->instanceNewSearchQuery()->field( 'open_timestamp' )
                ->limits( 1 )
                ->filters( $this->requestFilters )
                ->sort( array( 'open_timestamp' => 'asc' ) )
        );        
        $startDate = new DateTime();
        $startDate->setTimestamp( $startResult['SearchResult'][0]['fields'][$this->searchService->field( 'open_timestamp')] );

        $endResult = $this->searchService->query(
            $this->searchService->instanceNewSearchQuery()
                                ->field( 'open_timestamp' )
                                ->limits( 1 )
                                ->filters( $this->requestFilters )
                                ->sort( array( 'open_timestamp' => 'desc' ) )
        );
        $endDate = new DateTime();
        $endDate->setTimestamp( $endResult['SearchResult'][0]['fields'][$this->searchService->field( 'open_timestamp')] );

        $byMonthInterval = new DateInterval( 'P1M' );
        $byMonthPeriod = new DatePeriod( $startDate, $byMonthInterval, $endDate );

        $intervals = array();
        /** @var DateTime $month */
        foreach( $byMonthPeriod as $month )
        {
            $intervals[] = $this->getSolrIntervalArray( $month, $byMonthInterval );
        }

        $data = array();        
        foreach( $intervals as $interval )
        {
            $resultQuery = $this->searchService->instanceNewSearchQuery();
            $resultQuery->facetLimit = 1000;

            $result = $this->searchService->query(
                $resultQuery
                    ->field( 'internalId' )
                    ->filter(
                        'open',
                        "[{$interval['start']} TO {$interval['end']}]"
                    )
                    ->facets( $facets )
                    ->limits( 1 )
                    ->filters( $this->requestFilters )
                    ->sort( array( 'open_timestamp' => 'asc' ) )
            );
            $facetFields = $result['SearchExtras']->attribute( 'facet_fields' );            
            $facet = new stdClass;
            $facet->interval = $interval['_start']->format( 'm Y' );
            $facet->values = array();
            foreach( $facets as $index => $facetName )
            {
                $facet->values[$facetName] = $facetFields[$index]['countList'];
            }
            $data[] = $facet;            
        }
        return $data;
    }
    
    protected function getSolrIntervalArray( DateTime $startDateTime, DateInterval $interval )
    {
        $start = strftime( '%Y-%m-%dT%H:%M:%SZ', $startDateTime->format( 'U' ) );
        $startDateTime->add( $interval );
        $startDateTime->sub( new DateInterval( 'PT1S' ) );
        $end = strftime( '%Y-%m-%dT%H:%M:%SZ', $startDateTime->format( 'U' ) );
        return array( 'start' => $start, 'end' => $end, '_start' => $startDateTime );
    }
    
    protected function fecthAll( OpenContent\Sensor\Api\SearchQuery $query )
    {
        $query->limits( 1 );
        $result = $this->searchService->query( $query );

        if ( $result['SearchCount'] > $query->limits[0] )
        {
            $query->limits( $result['SearchCount'] );
            $result = $this->searchService->query( $query );
        }
        return $result;
    }
    
    public function typeData()
    {
        $data = array(
            'categories' => array(),
            'series' => array(),
            'title' => 'Numero di segnalazioni per tipologia'
            
        );
        $series = array();
        $facets = $this->getMonthlyFacets( array( 'type' ) );
        foreach( $facets as $facet )
        {            
            $data['categories'][] = $facet->interval;
            foreach( $facet->values as $name => $values )
                foreach( $values as $key => $value )
                    $series[$key][] = $value;
        }
        foreach( $series as $name => $serie )
        {
            $data['series'][] = array(
                'name' => $name,
                'data' => $serie
            );
        }
        return $data;
    }
    
    public function categoriesData()
    {
        $query = $this->searchService->instanceNewSearchQuery()                                     
                                     ->limits( 1 )
                                     ->filters( $this->requestFilters )
                                     ->facet( 'category_id_list' );

        $result = $this->searchService->query( $query );
        $facetFields =  $result['SearchExtras']->attribute( 'facet_fields' );
        $countList = $facetFields[0]['countList'];                
        $totalList = array_sum( $countList );
        
        $data = array(
            'title' => 'Aree tematiche',
            'series' => array(),
            'drilldown' => array()
        );
        
        $categoryTree = $this->repository->getCategoriesTree();
        foreach( $categoryTree->attribute( 'children' ) as $category )
        {
            $drilldown = array(
                'name' => $category->attribute( 'name' ),
                'id' => 'cat-' . $category->attribute( 'id' ),
                'data' => array()
            );
            $series = array(
                'name' => $category->attribute( 'name' ),
                'drilldown' => 'cat-' . $category->attribute( 'id' ),
                'y' => 0
            );
            $parentTotal = isset( $countList[$category->attribute( 'id' )] ) ? $countList[$category->attribute( 'id' )] : 0;            
            $childTotal = 0;
            foreach( $category->attribute( 'children' ) as $child )
            {
                $childTotal += isset( $countList[$child->attribute( 'id' )] ) ? $countList[$child->attribute( 'id' )] : 0;                
            }           
            foreach( $category->attribute( 'children' ) as $child )
            {                
                $childCount = isset( $countList[$child->attribute( 'id' )] ) ? $countList[$child->attribute( 'id' )] : 0;                
                $childPerc = $childTotal > 0 ? floatval( number_format( $childCount * 100 / $childTotal, 2 ) ) : 0;
                $drilldown['data'][] = array( $child->attribute( 'name' ), $childPerc );
            }
            $parentTotal += $childTotal;
            $series['y'] = floatval( number_format( $parentTotal * 100 / $totalList, 2 ) );
            
            $data['series'][] = $series;
            $data['drilldown'][] = $drilldown;
        }
        
        return $data;
    }
    
    public function areasData()
    {
        $query = $this->searchService->instanceNewSearchQuery()
                                     ->filters( $this->requestFilters )
                                     ->limits( 1 )
                                     ->facet( 'area_id_list' );

        $result = $this->searchService->query( $query );
        $facetFields =  $result['SearchExtras']->attribute( 'facet_fields' );
        $countList = $facetFields[0]['countList'];
        $totalList = array_sum( $countList );
        
        $data = array(
            'title' => 'Punti sulla mappa',
            'series' => array(),
            'drilldown' => array()
        );
        
        $areaTree = $this->repository->getAreasTree();
        foreach( $areaTree->attribute( 'children' ) as $firstArea )
        {
            foreach( $firstArea->attribute( 'children' ) as $area )
            {
                $drilldown = array(
                    'name' => $area->attribute( 'name' ),
                    'id' => 'area-' . $area->attribute( 'id' ),
                    'data' => array()
                );
                $series = array(
                    'name' => $area->attribute( 'name' ),
                    'drilldown' => 'area-' . $area->attribute( 'id' ),
                    'y' => 0
                );
                $parentTotal = isset( $countList[$area->attribute( 'id' )] ) ? $countList[$area->attribute( 'id' )] : 0;            
                $childTotal = 0;
                foreach( $area->attribute( 'children' ) as $child )
                {
                    $childTotal += isset( $countList[$child->attribute( 'id' )] ) ? $countList[$child->attribute( 'id' )] : 0;                
                }           
                foreach( $area->attribute( 'children' ) as $child )
                {                
                    $childCount = isset( $countList[$child->attribute( 'id' )] ) ? $countList[$child->attribute( 'id' )] : 0;                
                    $childPerc = floatval( number_format( $childCount * 100 / $childTotal, 2 ) );
                    $drilldown['data'][] = array( $child->attribute( 'name' ), $childPerc );
                }
                $parentTotal += $childTotal;
                $series['y'] = floatval( number_format( $parentTotal * 100 / $totalList, 2 ) );
                
                $data['series'][] = $series;
                $data['drilldown'][] = $drilldown;
            }
        }
        
        return $data;
    }
    
    public function statusData()
    {
        $query = $this->searchService->instanceNewSearchQuery()
                                     ->filters( $this->requestFilters )
                                     ->limits( 1 )
                                     ->facet( 'status' );

        $result = $this->searchService->query( $query );
        $facetFields =  $result['SearchExtras']->attribute( 'facet_fields' );
        $countList = $facetFields[0]['countList'];
        $totalList = array_sum( $countList );
        
        $data = array(
            'title' => 'Stati',
            'series' => array()
        );
        
        $states = $this->repository->getSensorPostStates( 'sensor' );
        foreach( $states as $state )
        {            
            $count = isset( $countList[$state->attribute( 'identifier' )] ) ? $countList[$state->attribute( 'identifier' )] : 0;
            $series = array(
                'name' => $state->attribute( 'current_translation' )->attribute( 'name' ),                
                'y' => floatval( number_format( $count * 100 / $totalList, 2 ) )
            );                            
            
            $data['series'][] = $series;                
        }
        
        return $data;
    }

    public function timesData()
    {
        $query = $this->searchService->instanceNewSearchQuery()
                                     ->fields(
                                         array(
                                             'id',
                                             'open_timestamp',
                                             'open_read_time',
                                             'read_assign_time',
                                             'assign_fix_time',
                                             'fix_close_time'
                                         )
                                     )
                                     ->filter( 'workflow_status', 'closed' )
                                     ->filters( $this->requestFilters )
                                     ->sort( array( 'open_timestamp' => 'asc' ) );
        $result = $this->fecthAll( $query );        

        $data = array(            
            'series' => array(),
            'title' => 'Tempi di lavorazione per segnalazione'
        );
        $series = array(
            'Lettura' => array(),
            'Assegnazione' => array(),
            'Conclusione' => array(),
            'Chiusura'  => array()
        );
        foreach( $result['SearchResult'] as $item )
        {
            $time = $item['fields'][$this->searchService->field( 'open_timestamp' )] * 1000;
            
            
            if ( isset( $item['fields'][$this->searchService->field( 'open_read_time' )] ) )
                $lettura = round( $item['fields'][$this->searchService->field( 'open_read_time' )] / 3600, 2 );
            else
                $lettura = 0;
            $series['Lettura'][] = array( $time, $lettura );
            
            if ( isset( $item['fields'][$this->searchService->field( 'read_assign_time' )] ) )
                $assegnazione = round($item['fields'][$this->searchService->field( 'read_assign_time' )] / 3600, 2 );
            else
                $assegnazione = 0;
            $series['Assegnazione'][] = array( $time, $assegnazione );                                
            
            if ( isset( $item['fields'][$this->searchService->field( 'assign_fix_time' )] ) )
                $conclusione = round($item['fields'][$this->searchService->field( 'assign_fix_time' )] / 3600, 2 );
            else
                $conclusione = 0;
            $series['Conclusione'][] = array( $time, $conclusione );   
            
            if ( isset( $item['fields'][$this->searchService->field( 'fix_close_time' )] ) )
                $chiusura = round($item['fields'][$this->searchService->field( 'fix_close_time' )] / 3600, 2 );
            else
                $chiusura = 0;            
            $series['Chiusura'][] = array( $time, $chiusura );   
        }
        
        $series = array_reverse( $series, true );
        
        foreach( $series as $key => $values )
        {
            $data['series'][] = array(
                'name' => $key,
                'data' => $values,
                'type' => 'column'
            );            
        }
        
        return $data;
    }

    public function timesAvgData()
    {
        $data = array(
            'categories' => array(),
            'series' => array(),
            'title' => 'Media mensile tempi di esecuzione'

        );
        $series = array();
        $facets = $this->getMonthlyFacets( array( 'open_read_time', 'read_assign_time', 'assign_fix_time', 'fix_close_time' ) );

        foreach( $facets as $facet )
        {
            $data['categories'][] = $facet->interval;
            foreach( $facet->values as $name => $values )
            {
                $array = array_keys( $values );
                $avg = count( $array ) > 0 ? array_sum( $array ) / count( $array ) : 0;
                if ( $name == 'open_read_time' ) $name = 'Lettura';
                if ( $name == 'read_assign_time' ) $name = 'Assegnazione';
                if ( $name == 'assign_fix_time' ) $name = 'Conclusione';
                if ( $name == 'fix_close_time' ) $name = 'Chiusura';
                $series[$name][] = round( $avg / 3600, 2 );
            }

        }

        $series = array_reverse( $series, true );

        foreach( $series as $name => $serie )
        {
            $data['series'][] = array(
                'name' => $name,
                'data' => $serie
            );
        }
        return $data;
    }

    public function performanceData()
    {
        $query = $this->searchService->instanceNewSearchQuery()
                                     ->fields(
                                         array( 'open_timestamp', 'reading_time', 'closing_time' )
                                     )
                                     ->filter( 'workflow_status', 'closed' )
                                     ->filters( $this->requestFilters )
                                     ->sort( array( 'open_timestamp' => 'asc' ) );

        $result = $this->fecthAll( $query );       

        $data = array();
        foreach ( $result['SearchResult'] as $item )
        {
            if ( isset( $item['fields'][$this->searchService->field( 'open_timestamp' )] ) )
            {
                $readingTime = null;
                if ( isset( $item['fields'][$this->searchService->field( 'reading_time' )] ) )
                {
                    $readingTime = $item['fields'][$this->searchService->field( 'reading_time' )] / 3600;
                }

                $closingTime = null;
                if ( isset( $item['fields'][$this->searchService->field( 'closing_time' )] ) )
                {
                    $closingTime = $item['fields'][$this->searchService->field( 'closing_time' )] / 3600;
                }

                $data[] = array(
                    $item['fields'][$this->searchService->field( 'open_timestamp' )] * 1000,
                    $readingTime,
                    $closingTime
                );
            }
        }

        return array(
            'title' => 'Tempi di risposta e di chiusura',
            'seriesName' => 'Minuti',
            'data' => $data
        );
    }

}