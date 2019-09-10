<?php

class SensorPostCsvExporter extends SearchQueryCSVExporter
{
    const MAX_DIRECT_DOWNLOAD_ITEMS = 0;

    protected $queryParams;

    protected $csvHeaders = array();

    protected $repository;

    public $filename;

    protected $downloadId;

    protected $iteration;

    public $options = array(
        'CSVDelimiter' => ';',
        'CSVEnclosure' => '"'
    );

    public function __construct(\Opencontent\Sensor\Legacy\Repository $repository)
    {
        $http = eZHTTPTool::instance();
        $this->repository = $repository;

        $this->queryParams = $_GET;
        $this->queryString = $this->queryParams['q'];
        $this->maxSearchLimit = \Opencontent\Sensor\Legacy\SearchService::MAX_LIMIT;

        $this->csvHeaders = array(
            'id' => ezpI18n::tr('sensor/export', 'ID'),
            'privacy' => ezpI18n::tr('sensor/export', 'Privacy'),
            'moderation' => ezpI18n::tr('sensor/export', 'Moderazione'),
            'type' => ezpI18n::tr('sensor/export', 'Tipo'),
            'current_status' => ezpI18n::tr('sensor/export', 'Stato corrente'),
            'created' => ezpI18n::tr('sensor/export', 'Creato il'),
            'modified' => ezpI18n::tr('sensor/export', 'Ultima modifica del'),
            'expiring_date' => ezpI18n::tr('sensor/export', 'Scadenza'),
            'resolution_time' => ezpI18n::tr('sensor/export', 'Data risoluzione'),
            'resolution_diff' => ezpI18n::tr('sensor/export', 'Tempo di risoluzione'),
            'title' => ezpI18n::tr('sensor/export', 'Titolo'),
            'author' => ezpI18n::tr('sensor/export', 'Autore'),
            'category' => ezpI18n::tr('sensor/export', 'Area tematica'),
            //'category_child' => ezpI18n::tr('sensor/export', 'Area tematica (descrittore)'),
            'current_owner' => ezpI18n::tr('sensor/export', 'Assegnatario'),
            'comment' => ezpI18n::tr('sensor/export', 'Commenti')
        );

        $this->filename = 'posts' . '_' . time();

        if ($http->hasGetVariable('download_id')) {
            $this->downloadId = $http->getVariable('download_id');
            $this->filename = $this->downloadId;
            $this->iteration = intval($http->getVariable('iteration'));
            if ($http->hasGetVariable('download')) {
                $this->download = true;
            }
        }
    }

    public function fetch()
    {
        return $this->repository->getSearchService()->searchPosts($this->queryString, $this->queryParams);
    }

    public function fetchCount()
    {
        if ($this->count === null) {
            $this->count = $this->repository->getSearchService()->searchPosts($this->queryString . ' and limit 1', $this->queryParams)->totalCount;
        }
        return $this->count;
    }

    protected function csvHeaders($item)
    {
        return array_values($this->csvHeaders);
    }

    /**
     * @param \Opencontent\Sensor\Api\Values\Post $item
     * @return array
     */
    function transformItem($post)
    {
        return array(
            'id' => $post->id,
            'privacy' => $post->privacy->name,
            'moderation' => $post->moderation->name,
            'type' => $post->type->name,
            'current_status' => $post->status->name,
            'created' => $post->published->format('d/m/Y H:i'),
            'modified' => $post->modified->format('d/m/Y H:i'),
            'expiring_date' => $post->expirationInfo->expirationDateTime->format('d/m/Y H:i'),
            'resolution_time' => $post->resolutionInfo->resolutionDateTime instanceof DateTime ? $post->resolutionInfo->resolutionDateTime->format('d/m/Y H:i') : '',
            'resolution_diff' => $post->resolutionInfo->resolutionDateTime instanceof DateTime ? $post->resolutionInfo->text : '',
            'title' => $post->subject,
            'author' => $post->author->name,
            'category' => count($post->categories) > 0 ? $post->categories[0]->name : '',
            //'category_child' => '', //@todo
            'current_owner' => $post->owners->count() > 0 ? $post->owners->first()->name : '',
            'comment' => $post->comments->count()
        );
    }

    protected function startPaginateDownload()
    {
        $this->tempFile($this->filename);

        echo $this->getPaginateTemplate(array_merge(
                $this->queryParams,
                array(
                    'query' => $this->queryString . ' and limit ' . $this->maxSearchLimit,
                    'download_id' => $this->filename,
                    'iteration' => 0,
                    'count' => $this->count,
                    'limit' => $this->maxSearchLimit
                )
            )
        );
    }

    protected function getPaginateTemplate($variables)
    {
        $tpl = eZTemplate::factory();
        foreach ($variables as $key => $value){
            if (is_string($value) && $value != 'true' && $value != 'false'){ //@todo
                $variables[$key] = '"' . $variables[$key] . '"';
            }
        }
        $tpl->setVariable('variables', $variables);

        return $tpl->fetch('design:sensor_api_gui/dashboard/download_paginate.tpl');
    }
}
