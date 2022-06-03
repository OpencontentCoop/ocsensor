<?php

class SensorBatchOperations
{
    private static $instance;

    private $handlers = [];

    private function __construct()
    {
        $this->handlers = [
            SensorBatchScenarioEditHandler::SENSOR_HANDLER_IDENTIFIER,
            SensorPostGroupParticipantHandler::SENSOR_HANDLER_IDENTIFIER,
            UserCsvImportHandler::SENSOR_HANDLER_IDENTIFIER,
        ];
    }

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new SensorBatchOperations();
        }

        return self::$instance;
    }

    public function hasActiveOperation($identifier)
    {
        $importCount = SQLIImportItem::count(SQLIImportItem::definition(), [
            'handler' => $identifier,
            'status' => [[SQLIImportItem::STATUS_PENDING, SQLIImportItem::STATUS_RUNNING]],
        ]);

        return $importCount > 0;
    }

    public function getHandlers()
    {
        return $this->handlers;
    }

    public function getOperations($offset = 0, $limit = 0, $filter = false)
    {
        $status = [];
        if ($filter == 'active'){
            $status = [[SQLIImportItem::STATUS_PENDING, SQLIImportItem::STATUS_RUNNING]];
        }elseif ($filter == 'pending'){
            $status = [[SQLIImportItem::STATUS_PENDING]];
        }elseif ($filter == 'failed'){
            $status = [[SQLIImportItem::STATUS_FAILED]];
        }elseif ($filter == 'canceled'){
            $status = [[SQLIImportItem::STATUS_CANCELED, SQLIImportItem::STATUS_INTERRUPTED]];
        }
        $conditions = [
            'handler' => [$this->getHandlers()],
        ];
        if (!empty($status)){
            $conditions['status'] = $status;
        }
        return SQLIImportItem::fetchList($offset, $limit, $conditions);
    }

    public function getOperationCount($filter = false)
    {
        $status = [];
        if ($filter == 'active'){
            $status = [[SQLIImportItem::STATUS_PENDING, SQLIImportItem::STATUS_RUNNING]];
        }elseif ($filter == 'pending'){
            $status = [[SQLIImportItem::STATUS_PENDING]];
        }elseif ($filter == 'failed'){
            $status = [[SQLIImportItem::STATUS_FAILED]];
        }elseif ($filter == 'canceled'){
            $status = [[SQLIImportItem::STATUS_CANCELED, SQLIImportItem::STATUS_INTERRUPTED]];
        }
        $conditions = [
            'handler' => [$this->getHandlers()],
        ];
        if (!empty($status)){
            $conditions['status'] = $status;
        }
        return SQLIImportItem::count(SQLIImportItem::definition(), $conditions);
    }

    public function addPendingOperation($identifier, $params)
    {
        $pendingImport = new SQLIImportItem([
            'handler' => $identifier,
            'user_id' => eZUser::currentUserID(),
        ]);
        $pendingImport->setAttribute('options', new SQLIImportHandlerOptions($params));
        $pendingImport->store();

        return $this;
    }

    public function run()
    {
        if(!SQLIImportToken::importIsRunning()) {
            exec('sh extension/ocsensor/bin/bash/run_batch_operations.sh');
        }
    }

    public function reRun($operationId)
    {
        $operation = SQLIImportItem::fetch((int)$operationId);
        if ($operation instanceof SQLIImportItem
            && !in_array($operation->attribute('status'), [SQLIImportItem::STATUS_PENDING, SQLIImportItem::STATUS_RUNNING])){
            $operation->setAttribute('status',SQLIImportItem::STATUS_PENDING);
            $operation->setAttribute('requested_time', time());
            $operation->store();
            $this->run();
        }
    }
}
