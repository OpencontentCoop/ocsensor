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
        )
    );

    public static function listAvailableCharts()
    {
        return self::$availableCharts;
    }

    public static function fetchChartByIdentifier( $identifier )
    {
        foreach( self::$availableCharts as $chart )
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

    public function performanceData()
    {
        $limit = 1000;
        $query = $this->searchService->instanceNewSearchQuery()
            ->fields( array( 'open_timestamp', 'reading_time', 'closing_time' ) )
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
        foreach( $result['SearchResult'] as $item )
        {
            if ( isset( $item['fields'][$this->searchService->field('open_timestamp')] ) )
            {
                $data[] = array(
                    $item['fields'][$this->searchService->field( 'open_timestamp' )] * 1000,
                    isset( $item['fields'][$this->searchService->field( 'reading_time' )] ) ? $item['fields'][$this->searchService->field( 'reading_time' )] / 60 : null,
                    isset( $item['fields'][$this->searchService->field( 'closing_time' )] ) ? $item['fields'][$this->searchService->field( 'closing_time' )]  / 60 : null
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