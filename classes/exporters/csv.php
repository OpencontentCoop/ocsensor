<?php

use Opencontent\Sensor\Api\Values\Participant;
use Opencontent\Sensor\Api\Values\Post\Channel;
use Opencontent\Sensor\Legacy\PermissionService;
use Opencontent\Sensor\Legacy\SearchService;
use Opencontent\Sensor\Legacy\Statistics\FiltersTrait;

class SensorPostCsvExporter extends SearchQueryCSVExporter
{
    use FiltersTrait;

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
        unset($this->queryParams['/sensor/dashboard/(export)']);
        unset($this->queryParams['/sensor/export']);

        $this->queryString = isset($this->queryParams['query']) ? $this->queryParams['query'] : $this->queryParams['q'];
        $this->maxSearchLimit = SearchService::MAX_LIMIT;

        $categoryFilter = $this->getCategoryFilter();
        $rangeFilter = $this->getRangeFilter();
        $areaFilter = $this->getAreaFilter();
        $groupFilter = $this->getOwnerGroupFilter();
        $this->queryString = "{$categoryFilter}{$rangeFilter}{$areaFilter}{$groupFilter}" . $this->queryString;

        unset($this->queryParams['capabilities']); //boost performance
        if (isset($this->queryParams['ignorePolicies']) && $this->queryParams['ignorePolicies']){
            $this->searchPolicies = [];
        }

        $this->csvHeaders = array(
            // https://answers.microsoft.com/en-us/office/forum/office_2013_release-excel/how-to-change-the-way-excel-scans-a-textcsv-file/24741ea7-5490-4d9a-a6b0-7728098330a2?auth=1
            'id' => ezpI18n::tr('sensor/export', 'Id'),
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
//            'fiscal_code' => ezpI18n::tr('sensor/export', 'Codice Fiscale'),
            'category' => ezpI18n::tr('sensor/export', 'Categoria'),
            'category_child' => ezpI18n::tr('sensor/export', 'Categoria (descrittore)'),
            'current_owner_group' => ezpI18n::tr('sensor/export', 'Gruppo incaricato'),
            'current_owner' => ezpI18n::tr('sensor/export', 'Incaricato'),
            'comment' => ezpI18n::tr('sensor/export', 'Commenti'),
            'channel' => ezpI18n::tr('sensor/export', 'Canale'),
            'area' => ezpI18n::tr('sensor/export', 'Zona'),
            'response' => ezpI18n::tr('sensor/export', 'Risposta'),
            'response_count' => ezpI18n::tr('sensor/export', 'Risposte'),
            'message_count' => ezpI18n::tr('sensor/export', 'Note'),
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

    public function getParameter($name)
    {
        return eZHTTPTool::instance()->hasGetVariable($name) ? eZHTTPTool::instance()->getVariable($name) : null;
    }

    public function hasParameter($name)
    {
        return eZHTTPTool::instance()->hasGetVariable($name) && !empty(eZHTTPTool::instance()->getVariable($name));
    }

    public function fetch()
    {
        return $this->repository->getSearchService()->searchPosts($this->queryString, $this->queryParams, $this->searchPolicies);
    }

    public function fetchCount()
    {
        if ($this->count === null) {
            $searchQuery = '';
            if (!empty($this->queryString)){
                $searchQuery = $this->queryString . ' and ';
            }
            $this->count = $this->repository->getSearchService()->searchPosts($searchQuery . 'limit 1', $this->queryParams, $this->searchPolicies)->totalCount;
        }
        return $this->count;
    }

    protected function csvHeaders($item)
    {
        return array_values($this->csvHeaders);
    }

    /**
     * @param \Opencontent\Sensor\Api\Values\Post $post
     * @return array
     */
    function transformItem($post)
    {
        if ($post instanceof \Opencontent\Sensor\Api\Values\Post) {
            $ownerGroup = $post->latestOwnerGroup ?
                $post->latestOwnerGroup->name :
                implode(' - ', $post->owners->getParticipantNameListByType(Participant::TYPE_GROUP));

            $owner = $post->latestOwner instanceof Participant ?
                $post->latestOwner->name :
                implode(' - ', $post->owners->getParticipantNameListByType(Participant::TYPE_USER));

            $item = array(
                'id' => $post->id,
                'privacy' => $post->privacy->name,
                'moderation' => $post->moderation->name,
                'type' => $post->type->name,
                'current_status' => $post->status->name,
                'created' => $post->published->format('d/m/Y H:i'),
                'modified' => $post->modified->format('d/m/Y H:i'),
                'expiring_date' => $post->expirationInfo->expirationDateTime->format('d/m/Y H:i'),
                'resolution_time' => $post->resolutionInfo && $post->resolutionInfo->resolutionDateTime instanceof DateTime ? $post->resolutionInfo->resolutionDateTime->format('d/m/Y H:i') : '',
                'resolution_diff' => $post->resolutionInfo && $post->resolutionInfo->resolutionDateTime instanceof DateTime ? $post->resolutionInfo->text : '',
                'title' => $post->subject,
                'author' => $post->author->name,
//                'fiscal_code' => $post->author->fiscalCode,
                'category' => count($post->categories) > 0 ? $this->mainCategories[$post->categories[0]->id] : '',
                'category_child' => count($post->categories) > 0 && isset($this->childCategories[$post->categories[0]->id]) ? $this->childCategories[$post->categories[0]->id] : '',
                'current_owner_group' => $ownerGroup,
                'current_owner' => $owner,
                'comment' => $post->comments->count(),
                'channel' => $post->channel instanceof Channel ? $post->channel->name : '',
                'area' => count($post->areas) > 0 ? $post->areas[0]->name : '',
                'response' => $post->responses->count() > 0 ? $post->responses->last()->text : '',
                'response_count' => $post->responses->count(),
                'message_count' => $post->privateMessages->count(),
            );

            if ($this->searchPolicies !== null && ($post->privacy->identifier != 'public' || $post->moderation->identifier == 'waiting')){
                $item['title'] = '***';
                $item['author'] = '***';
                $item['response'] = '***';
            }

            if (
                ($this->repository->getSensorSettings()->get('HideTimelineDetails') || $this->repository->getSensorSettings()->get('HideOperatorNames'))
                && $this->repository->getCurrentUser()->type == 'user'
                && !PermissionService::isSuperAdmin($this->repository->getCurrentUser())
            ){
                $item['current_owner'] = '';
            }

            return $item;
        }

        return [];
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
                $variables[$key] = '"' . addcslashes($variables[$key], '"') . '"';
            }elseif (is_bool($value)){
                $variables[$key] = (int)$variables[$key];
            }
        }
        $tpl->setVariable('variables', $variables);

        return $tpl->fetch('design:sensor_api_gui/dashboard/download_paginate.tpl');
    }

    protected function tempFileName($filename)
    {
        return eZSys::storageDirectory() . '/export-csv/' . $filename . '.csv';
    }

    protected function tempFile($filename)
    {
        $filename = $this->tempFileName($filename);
        $fileHandler = eZClusterFileHandler::instance($filename);
        if (!$fileHandler->exists()) {
            $fileHandler->storeContents(' ', 'exportcsv', 'text/csv');
        }

        return $fileHandler;
    }

    protected function handlePaginateDownload()
    {
        $fileHandler = $this->tempFile($this->filename);

        $tempFilename = eZSys::storageDirectory() . '/export-csv/' . uniqid('exportaspaginate_') . '.temp';
        $contents = $fileHandler->fetchContents();
        if ($contents === ' '){
            $contents = '';
        }
        if (!eZFile::create(basename($tempFilename), dirname($tempFilename), $contents)){
            eZDebug::writeError("Fail creating $tempFilename", __METHOD__);
        }

        $output = fopen($tempFilename, 'a');
        $result = $this->fetch();

        $makeHeaders = $this->iteration == 0;

        foreach ($result->searchHits as $item) {
            $headers = $this->csvHeaders($item);
            if ($makeHeaders) {
                fputcsv(
                    $output,
                    $headers,
                    $this->options['CSVDelimiter'],
                    $this->options['CSVEnclosure']
                );
            }
            $serialized = $this->transformItem($item);
            if (!empty($serialized)){
                $values[] = $serialized;
            }
            fputcsv($output, array_values($values), $this->options['CSVDelimiter'], $this->options['CSVEnclosure']);
            $makeHeaders = false;
        }

        $this->queryString = $result->nextPageQuery;

        $fileHandler->storeContents( file_get_contents($tempFilename) );
        unlink($tempFilename);

        $data = array_merge(
            $this->queryParams,
            array(
                'query' => $this->queryString,
                'download_id' => $this->filename,
                'iteration' => ++$this->iteration,
                'last' => count( $result->searchHits ),
                'count' => $this->count,
                'limit' => $this->maxSearchLimit
            )
        );

        header('Content-Type: application/json');
        echo json_encode($data);
        eZExecution::cleanExit();
    }
}
