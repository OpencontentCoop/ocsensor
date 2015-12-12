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
            $functionName = strtolower( $this->parameters['type'] ) . 'Data';
            if ( method_exists( $this, $functionName ) )
            {
                $data = $this->$functionName();
            }
        }
        return $data;
    }

    public function performanceData()
    {
        $query = $this->searchService->instanceNewSearchQuery()
            ->fields( array( 'open_timestamp', 'reading_time', 'closing_time' ) )
            ->filter( 'workflow_status', 'closed' )
            ->limits( 1000 )
            ->sort( array( 'open_timestamp' => 'asc' ) );

        $result = $this->searchService->query( $query );

        if ( $result['SearchCount'] > 1000 )
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