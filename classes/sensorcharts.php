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

    protected static $availableCharts = array(
        array(
            'identifier' => 'performance',
            'name' => 'Tempi di risposta e di chiusura',
            'template_uri' => 'design:sensor/charts/performance.tpl',
            'call_method' => 'performanceData'
        ),
        array(
            'identifier' => 'type',
            'name' => 'Tipologia di segnalzione',
            'template_uri' => 'design:sensor/charts/type.tpl',
            'call_method' => 'typeData'
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

    public function typeData()
    {
        $startResult = $this->searchService->query(
            $this->searchService->instanceNewSearchQuery()
                                ->fields( array( 'open_timestamp' ) )
                                ->limits( 1 )
                                ->sort( array( 'open_timestamp' => 'asc' ) )
        );
        $startDate = new DateTime();
        $startDate->setTimestamp( $startResult['SearchResult'][0]['open_timestamp'] );

        $endResult = $this->searchService->query(
            $this->searchService->instanceNewSearchQuery()
                                ->fields( array( 'open_timestamp' ) )
                                ->limits( 1 )
                                ->sort( array( 'open_timestamp' => 'desc' ) )
        );
        $endDate = new DateTime();
        $endDate->setTimestamp( $endResult['SearchResult'][0]['open_timestamp'] );

        $byMonthInterval = new DateInterval( 'P1M' );
        $byMonthPeriod = new DatePeriod( $startDate, $byMonthInterval, $endDate );

        $intervals = array( $this->getSolrIntervalArray( $startDate, $byMonthInterval ) );
        /** @var DateTime $month */
        foreach( $byMonthPeriod as $month )
        {
            $intervals[] = $this->getSolrIntervalArray( $month, $byMonthInterval );
        }

        $data = array(
            'categories' => array(),
            'series' => array(),
        );
        foreach( $intervals as $interval )
        {
            $result = $this->searchService->query(
                $this->searchService->instanceNewSearchQuery()
                                    ->filter(
                                        $this->searchService->field( 'open_timestamp' ),
                                        "[{$interval['start']}*{$interval['end']}]"
                                    )
                                    ->facet( 'type' )
                                    ->limits( 1 )
                                    ->sort( array( 'open_timestamp' => 'asc' ) )
            );
        }
    }

    protected function getSolrIntervalArray( DateTime $startDateTime, DateInterval $interval )
    {
        $start = strftime( '%Y-%m-%dT%H:%M:%SZ', $startDateTime->format( 'U' ) );
        $startDateTime->add( $interval );
        $startDateTime->sub( new DateInterval( 'PT1S' ) );
        $end = strftime( '%Y-%m-%dT%H:%M:%SZ', $startDateTime->format( 'U' ) );
        return array( $start, $end );
    }

    public function performanceData()
    {
        $limit = 1000;
        $query = $this->searchService->instanceNewSearchQuery()
                                     ->fields(
                                         array( 'open_timestamp', 'reading_time', 'closing_time' )
                                     )
                                     ->filter( 'workflow_status', 'closed' )
                                     ->limits( $limit )
                                     ->sort( array( 'open_timestamp' => 'asc' ) );

        $result = $this->searchService->query( $query );

        if ( $result['SearchCount'] > $limit )
        {
            $query->limits( $result['SearchCount'] );
            $result = $this->searchService->query( $query );
        }

        $data = array();
        foreach ( $result['SearchResult'] as $item )
        {
            if ( isset( $item['fields'][$this->searchService->field( 'open_timestamp' )] ) )
            {
                $readingTime = null;
                if ( isset( $item['fields'][$this->searchService->field( 'reading_time' )] ) )
                {
                    $readingTime = $item['fields'][$this->searchService->field( 'reading_time' )] / 60;
                }

                $closingTime = null;
                if ( isset( $item['fields'][$this->searchService->field( 'closing_time' )] ) )
                {
                    $closingTime = $item['fields'][$this->searchService->field( 'closing_time' )] / 60;
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