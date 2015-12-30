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

    protected $requestExtras = array();

    protected static $availableCharts = array(
        array(
            'identifier' => 'status',
            'name' => 'Numero totale e stato corrente',
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
            'name' => 'Quartieri',
            'template_uri' => 'design:sensor/charts/areas.tpl',
            'call_method' => 'areasData'
        ),
        array(
            'identifier' => 'type',
            'name' => 'Tipologia di segnalazione',
            'template_uri' => 'design:sensor/charts/type.tpl',
            'call_method' => 'typeData'
        ),
//        array(
//            'identifier' => 'times',
//            'name' => 'Tempi di esecuzione',
//            'template_uri' => 'design:sensor/charts/times.tpl',
//            'call_method' => 'timesData'
//        ),
        array(
            'identifier' => 'timesAvg',
            'name' => 'Media tempi di esecuzione',
            'template_uri' => 'design:sensor/charts/times_avg.tpl',
            'call_method' => 'timesAvgData'
        ),
//        array(
//            'identifier' => 'performance',
//            'name' => 'Tempi di risposta e di chiusura',
//            'template_uri' => 'design:sensor/charts/performance.tpl',
//            'call_method' => 'performanceData'
//        )
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
            foreach( $this->parameters['filters'] as $filter )
            {
                $name = $filter['name'];
                $value = $filter['value'];
                if ( !empty( $value ) )
                {
                    if ( $this->searchService->field( $name ) )
                        $this->requestFilters[$name][] = $value;
                    else
                        $this->requestExtras[$name][] = $value;
                }
            }
//            foreach( $filters as $name => $values )
//            {
//                $allFilters = $values;
//                foreach( $values as $value )
//                    $allFilters = array_merge(
//                        $allFilters,
//                        $this->getSubFilters( $name, $value )
//                    );
//                $this->requestFilters[$name] = array_unique( $allFilters );
//            }
        }
    }

    protected function getSubFilters( $name, $id )
    {
        $data = array();
        $tree = array();

        if ( $name == 'category_id_list' )
            $tree = $this->repository->getCategoriesTree()->attribute( 'children' );

        if ( $name == 'area_id_list' )
            $tree = $this->repository->getAreasTree()->attribute( 'children' );

        foreach( $tree as $item )
        {
            if ( $item->attribute( 'id' ) == $id && count( $item->attribute( 'children' ) ) > 0 )
            {
                foreach( $item->attribute( 'children' ) as $child )
                {
                    $data[] = $child->attribute( 'id' );
                }
            }
        }

        return $data;
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
        elseif ( isset( $this->parameters['debug'] ) )
        {
            $data = $this->debugData();
        }

        return $data;
    }
    
    protected function debugData()
    {
        return array();
    }

    protected function getIntervalFacets( $intervalString, $facets )
    {
        $startResult = $this->searchService->query(
            $this->searchService->instanceNewSearchQuery()->field( 'open_timestamp' )
                ->limits( 1 )
                ->filters( $this->requestFilters )
                ->sort( array( 'open_timestamp' => 'asc' ) )
        );

        if ( $startResult['SearchCount'] == 0 )
            return array();

        $startDate = new DateTime();
        $startDate->setTimestamp( $startResult['SearchResult'][0]['fields'][$this->searchService->field( 'open_timestamp')] );
        $startDate->setDate( $startDate->format( 'Y' ), 1, 1 );
        $startDate->setTime( 0, 0 );

        $endResult = $this->searchService->query(
            $this->searchService->instanceNewSearchQuery()
                                ->field( 'open_timestamp' )
                                ->limits( 1 )
                                ->filters( $this->requestFilters )
                                ->sort( array( 'open_timestamp' => 'desc' ) )
        );
        $endDate = new DateTime();
        $endDate->setTimestamp( $endResult['SearchResult'][0]['fields'][$this->searchService->field( 'open_timestamp')] );
        $endDate->setDate( $startDate->format( 'Y' ), 12, 31 );
        $endDate->setTime( 23, 59 );


        switch ( $intervalString  )
        {
            case 'quarterly':
            {
                $byInterval = new DateInterval( 'P3M' );
            } break;

            case 'half-yearly':
            {
                $byInterval = new DateInterval( 'P6M' );
            } break;

            case 'yearly':
            {
                $byInterval = new DateInterval( 'P1Y' );
            } break;

            default:
                $byInterval = new DateInterval( 'P1M' );
        }

        $byPeriod = new DatePeriod( $startDate, $byInterval, $endDate );

        $intervals = array();
        /** @var DateTime $month */
        foreach( $byPeriod as $period )
        {
            $intervals[] = $this->getSolrIntervalArray( $period, $byInterval );
        }

        $availableFacets = $this->getAvailableFacetsKeys( $facets );
        $data = array();        
        foreach( $intervals as $interval )
        {
            $resultQuery = $this->searchService->instanceNewSearchQuery();
            $resultQuery->facetLimit = 1000;
            $resultQuery
                ->field( 'internalId' )
                ->filter(
                    'open',
                    "[{$interval['start']} TO {$interval['end']}]"
                )
                ->facets( $facets )
                ->limits( 1 )
                ->filters( $this->requestFilters )
                ->sort( array( 'open_timestamp' => 'asc' ) );

            $result = $this->searchService->query( $resultQuery );
            $facetFields = $result['SearchExtras']->attribute( 'facet_fields' );            
            $facet = new stdClass;
            $facet->interval = $interval['_start']->format( 'm/Y' );
            $facet->values = array();
            foreach( $facets as $index => $facetName )
            {
                $values = array();
                foreach( $availableFacets[$facetName] as $key )
                {
                    if ( isset( $facetFields[$index]['countList'][$key] ) && !empty( $facetFields[$index]['countList'][$key] ))
                        $values[$key] = $facetFields[$index]['countList'][$key];
                    else
                        $values[$key] = 0;
                }
                $facet->values[$facetName] = $values;
            }
            $data[] = $facet;            
        }
        return $data;
    }

    protected function getAvailableFacetsKeys( $facets )
    {
        $resultQuery = $this->searchService->instanceNewSearchQuery();
        $resultQuery->facetLimit = 1000;
        $resultQuery
            ->field( 'internalId' )
            ->facets( $facets )
            ->limits( 1 )
            ->filters( $this->requestFilters )
            ->sort( array( 'open_timestamp' => 'asc' ) );
        $result = $this->searchService->query( $resultQuery );
        $facetFields = $result['SearchExtras']->attribute( 'facet_fields' );
        $data = array();
        foreach( $facets as $index => $facetName )
        {
            $data[$facetName] = array_keys( $facetFields[$index]['countList'] );
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

        $intervalString = 'monthly';
        if ( isset( $this->requestExtras['_interval'][0] ) )
            $intervalString = $this->requestExtras['_interval'][0];

        $facets = $this->getIntervalFacets( $intervalString, array( 'type' ) );
        foreach( $facets as $facet )
        {            
            $data['categories'][] = $facet->interval;
            foreach( $facet->values as $name => $values )
            {
                if ( count( $values ) > 0 )
                {
                    foreach ( $values as $key => $value )
                    {
                        $series[$key][] = $value;
                    }
                }
            }
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
        $data = array(
            'categories' => array(),
            'series' => array(),
            'title' => 'Numero di segnalazioni per area tematica'

        );
        $series = array();

        $intervalString = 'monthly';
        if ( isset( $this->requestExtras['_interval'][0] ) )
            $intervalString = $this->requestExtras['_interval'][0];

        $facets = $this->getIntervalFacets( $intervalString, array( 'category_id_list' ) );
        foreach( $facets as $facet )
        {
            $data['categories'][] = $facet->interval;
            foreach( $facet->values as $name => $values )
            {
                if ( count( $values ) > 0 )
                {
                    foreach ( $values as $key => $value )
                    {
                        $key = $this->getCategoryNameById( $key );
                        $series[$key][] = $value;
                    }
                }
            }
        }
        ksort( $series );
        foreach( $series as $name => $serie )
        {
            $data['series'][] = array(
                'name' => $name,
                'data' => $serie
            );
        }
        return $data;
    }

    protected function getCategoryNameById( $id )
    {
        $categoryTree = $this->repository->getCategoriesTree();
        foreach ( $categoryTree->attribute( 'children' ) as $category )
        {
            if ( $category->attribute( 'id' ) == $id )
            {
                return $category->attribute( 'name' );
            }
            foreach ( $category->attribute( 'children' ) as $child )
            {
                if ( $child->attribute( 'id' ) == $id )
                {
                    return $child->attribute( 'name' );
                }
            }
        }

        return $id;
    }

    protected function getAreaNameById( $id )
    {
        $areaTree = $this->repository->getAreasTree();
        foreach ( $areaTree->attribute( 'children' ) as $area )
        {
            if ( $area->attribute( 'id' ) == $id )
            {
                return $area->attribute( 'name' );
            }
            foreach ( $area->attribute( 'children' ) as $child )
            {
                if ( $child->attribute( 'id' ) == $id )
                {
                    return $child->attribute( 'name' );
                }
            }
        }

        return $id;
    }

    public function areasData()
    {
        $data = array(
            'categories' => array(),
            'series' => array(),
            'title' => 'Numero di segnalazioni per quartiere'

        );
        $series = array();

        $intervalString = 'monthly';
        if ( isset( $this->requestExtras['_interval'][0] ) )
            $intervalString = $this->requestExtras['_interval'][0];

        $facets = $this->getIntervalFacets( $intervalString, array( 'area_id_list' ) );
        foreach( $facets as $facet )
        {
            $data['categories'][] = $facet->interval;
            foreach( $facet->values as $name => $values )
            {
                if ( count( $values ) > 0 )
                {
                    foreach ( $values as $key => $value )
                    {
                        $series[$key][] = $value;
                    }
                }
            }
        }
        foreach( $series as $name => $serie )
        {
            $name = $this->getAreaNameById( $name );
            $data['series'][] = array(
                'name' => $name,
                'data' => $serie
            );
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
            'title' => 'Numero totale e stato corrente',
            'series' => array()
        );
        
        $states = $this->repository->getSensorPostStates( 'sensor' );
        foreach( $states as $state )
        {            
            $count = isset( $countList[$state->attribute( 'identifier' )] ) ? $countList[$state->attribute( 'identifier' )] : 0;
            $series = array(
                'name' => $state->attribute( 'current_translation' )->attribute( 'name' ) . ' ' . $count,
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
            'Lavorazione' => array(),
            'Chiusura'  => array()
        );
        foreach( $result['SearchResult'] as $item )
        {
            $time = $item['fields'][$this->searchService->field( 'open_timestamp' )] * 1000;
            
            
            if ( isset( $item['fields'][$this->searchService->field( 'open_read_time' )] ) )
                $lettura = $this->secondsInDay( $item['fields'][$this->searchService->field( 'open_read_time' )] );
            else
                $lettura = 0;
            $series['Lettura'][] = array( $time, $lettura );
            
            if ( isset( $item['fields'][$this->searchService->field( 'read_assign_time' )] ) )
                $assegnazione = $this->secondsInDay( $item['fields'][$this->searchService->field( 'read_assign_time' )] );
            else
                $assegnazione = 0;
            $series['Assegnazione'][] = array( $time, $assegnazione );                                
            
            if ( isset( $item['fields'][$this->searchService->field( 'assign_fix_time' )] ) )
                $lavorazione = $this->secondsInDay( $item['fields'][$this->searchService->field( 'assign_fix_time' )] );
            else
                $lavorazione = 0;
            $series['Lavorazione'][] = array( $time, $lavorazione );
            
            if ( isset( $item['fields'][$this->searchService->field( 'fix_close_time' )] ) )
                $chiusura = $this->secondsInDay( $item['fields'][$this->searchService->field( 'fix_close_time' )] );
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

    //@todo
    protected function secondsInDay( $seconds )
    {
        return round( $seconds / 3600 / 24, 1 );
    }

    public function timesAvgData()
    {
        $data = array(
            'categories' => array(),
            'series' => array(),
            'title' => 'Media tempi di esecuzione'

        );
        $series = array();

        $this->requestFilters['status'] = 'close';

        $intervalString = 'monthly';
        if ( isset( $this->requestExtras['_interval'][0] ) )
            $intervalString = $this->requestExtras['_interval'][0];

        $facets = $this->getIntervalFacets( $intervalString, array( 'open_read_time', 'read_assign_time', 'assign_fix_time', 'fix_close_time' ) );

        foreach( $facets as $facet )
        {
            $data['categories'][] = $facet->interval;
            foreach( $facet->values as $name => $values )
            {
                $sum = array();
                $count = 0;

                foreach( $values as $key => $value )
                {
                    $sum[] = $key * $value;
                    $count += $value;
                }
                $sum = array_sum( $sum );

                $avg = $count > 0 ? $sum / $count : 0;
                if ( $name == 'open_read_time' ) $name = 'Lettura';
                if ( $name == 'read_assign_time' ) $name = 'Assegnazione';
                if ( $name == 'assign_fix_time' ) $name = 'Lavorazione';
                if ( $name == 'fix_close_time' ) $name = 'Chiusura';
                $series[$name][] = $this->secondsInDay( $avg );
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
                    $readingTime = $this->secondsInDay( $item['fields'][$this->searchService->field( 'reading_time' )] );
                }

                $closingTime = null;
                if ( isset( $item['fields'][$this->searchService->field( 'closing_time' )] ) )
                {
                    $closingTime = $this->secondsInDay( $item['fields'][$this->searchService->field( 'closing_time' )] );
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