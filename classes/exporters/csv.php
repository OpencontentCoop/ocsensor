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

    protected $mainCategories;

    protected $childCategories;

    protected $searchPolicies;

    public $options = array(
        'CSVDelimiter' => ';',
        'CSVEnclosure' => '"'
    );

    public function __construct(\Opencontent\Sensor\Legacy\Repository $repository)
    {
        $http = eZHTTPTool::instance();
        $this->repository = $repository;

        $this->queryParams = $http->attribute('get');
        $this->queryString = $this->queryParams['q'];
        $this->maxSearchLimit = \Opencontent\Sensor\Legacy\SearchService::MAX_LIMIT;

        unset($this->queryParams['capabilities']); //boost performance
        if (isset($this->queryParams['ignorePolicies']) && $this->queryParams['ignorePolicies']){
            $this->searchPolicies = [];
        }

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
            'category' => ezpI18n::tr('sensor/export', 'Categoria'),
            'category_child' => ezpI18n::tr('sensor/export', 'Categoria (descrittore)'),
            'current_owner' => ezpI18n::tr('sensor/export', 'Assegnatario'),
            'comment' => ezpI18n::tr('sensor/export', 'Commenti')
        );

        $this->filename = 'posts' . '_' . time();

        $this->mainCategories = [];
        $this->childCategories = [];
        $categoryTree = $this->repository->getCategoriesTree();
        foreach ($categoryTree->attribute('children') as $categoryTreeItem){
            $this->mainCategories[$categoryTreeItem->attribute('id')] = $categoryTreeItem->attribute('name');
            foreach ($categoryTreeItem->attribute('children') as $categoryTreeItemChild) {
                $this->mainCategories[$categoryTreeItemChild->attribute('id')] = $categoryTreeItem->attribute('name');
                $this->childCategories[$categoryTreeItemChild->attribute('id')] = $categoryTreeItemChild->attribute('name');
            }
        }

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
        return $this->repository->getSearchService()->searchPosts($this->queryString, $this->queryParams, $this->searchPolicies);
    }

    public function fetchCount()
    {
        if ($this->count === null) {
            $this->count = $this->repository->getSearchService()->searchPosts($this->queryString . ' and limit 1', $this->queryParams, $this->searchPolicies)->totalCount;
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
        if ($post instanceof \Opencontent\Sensor\Api\Values\Post) {
            $item = array(
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
                'category' => count($post->categories) > 0 ? $this->mainCategories[$post->categories[0]->id] : '',
                'category_child' => count($post->categories) > 0 && isset($this->childCategories[$post->categories[0]->id]) ? $this->childCategories[$post->categories[0]->id] : '',
                'current_owner' => $post->owners->count() > 0 ? $post->owners->first()->name : '',
                'comment' => $post->comments->count()
            );

            if ($this->searchPolicies !== null && ($post->privacy->identifier != 'public' || $post->moderation->identifier == 'waiting')){
                $item['title'] = '***';
                $item['author'] = '***';
            }

            return $item;
        }
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
