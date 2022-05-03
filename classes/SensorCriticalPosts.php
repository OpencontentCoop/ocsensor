<?php

class SensorCriticalPosts
{
    private static $isSchemaInstalled;

    private static function isSchemaInstalled($tableName)
    {
        if (!isset(self::$isSchemaInstalled[$tableName])) {
            $dbName = eZINI::instance()->variable('DatabaseSettings', 'Database');
            $res = eZDB::instance()->arrayQuery(
                "SELECT EXISTS (SELECT FROM information_schema.tables WHERE  table_catalog = '$dbName' AND table_name   = '$tableName');"
            );
            self::$isSchemaInstalled[$tableName] = $res[0]['exists'] == 't';
        }

        return self::$isSchemaInstalled[$tableName];
    }

    private static function installSchemaIfNeeded()
    {
        $db = eZDB::instance();

        if (!self::isSchemaInstalled('ocsensor_closing_time')) {
            $db->query(
                "CREATE OR REPLACE VIEW ocsensor_closing_time AS
            SELECT DISTINCT ON (ezcollab_item.data_int1) ezcollab_item.data_int1 AS post_id, ezcollab_item.id AS internal_id, ezcollab_simple_message.created AS closed_at
                FROM ezcollab_item
                  JOIN ezcollab_item_message_link ON (ezcollab_item.id = ezcollab_item_message_link.collaboration_id)
                  JOIN ezcollab_simple_message ON (ezcollab_simple_message.id = ezcollab_item_message_link.message_id)
                WHERE ezcollab_item.status = 2
                  AND ezcollab_simple_message.data_text1 LIKE '_closed%'
                ORDER BY ezcollab_item.data_int1, ezcollab_simple_message.created DESC"
            );
        }

        if (!self::isSchemaInstalled('ocsensor_commented_after_closed')) {
            $db->query(
                "CREATE OR REPLACE VIEW ocsensor_commented_after_closed AS
            SELECT ocsensor_closing_time.post_id, ezcollab_item_message_link.created AS commented_at FROM ocsensor_closing_time
                  JOIN ezcollab_item_message_link ON (ocsensor_closing_time.internal_id = ezcollab_item_message_link.collaboration_id)
                  WHERE ezcollab_item_message_link.created > ocsensor_closing_time.closed_at AND ezcollab_item_message_link.message_type = 1"
            );
        }

        if (!self::isSchemaInstalled('ocsensor_current_post_status')) {
            $postClassId = (int)eZContentClass::classIDByIdentifier(OpenPaSensorRepository::instance()->getPostContentClassIdentifier());
            $sensorStates = OpenPaSensorRepository::instance()->getSensorPostStates('sensor');
            $db->query(
                "CREATE OR REPLACE VIEW ocsensor_current_post_status AS
            SELECT DISTINCT ON (ezcontentobject.id) ezcontentobject.id, ezcontentobject.published, ezcontentobject_name.name, CASE
                    WHEN contentobject_state_id = " . $sensorStates['sensor.pending']->attribute('id') . " THEN '" . $sensorStates['sensor.pending']->attribute('identifier') . "'
                    WHEN contentobject_state_id = " . $sensorStates['sensor.open']->attribute('id') . " THEN '" . $sensorStates['sensor.open']->attribute('identifier') . "'
                    WHEN contentobject_state_id = " . $sensorStates['sensor.close']->attribute('id') . " THEN '" . $sensorStates['sensor.close']->attribute('identifier') . "'
                END contentobject_state_id
                FROM ezcontentobject_tree
                  INNER JOIN ezcontentobject ON (ezcontentobject_tree.contentobject_id = ezcontentobject.id)
                  INNER JOIN ezcontentobject_name ON ( ezcontentobject_tree.contentobject_id = ezcontentobject_name.contentobject_id AND ezcontentobject_tree.contentobject_version = ezcontentobject_name.content_version)
                  INNER JOIN ezcobj_state_link ON (ezcobj_state_link.contentobject_id = ezcontentobject.id)
                  INNER JOIN ezcollab_item ON (ezcollab_item.data_int1 = ezcontentobject.id)
                WHERE
                 ezcontentobject.contentclass_id = $postClassId AND ezcontentobject_tree.node_id = ezcontentobject_tree.main_node_id 
                 AND ezcollab_item.type_identifier = 'openpasensor'
                 AND ezcontentobject.status = 1
                 AND ezcontentobject.language_mask & 15 > 0
                 AND ezcobj_state_link.contentobject_state_id IN (" . $sensorStates['sensor.pending']->attribute('id') . "," . $sensorStates['sensor.open']->attribute('id') . "," . $sensorStates['sensor.close']->attribute('id') . ")
                ORDER BY ezcontentobject.id ASC"
            );
        }

        if (!self::isSchemaInstalled('ocsensor_latest_group_assignment')) {
            $db->query(
                "CREATE OR REPLACE VIEW ocsensor_latest_group_assignment AS
            SELECT post_id, name, reference FROM(
                  SELECT DISTINCT ON (post_id) post_id, target_group_id from ocsensor_timeline
                    WHERE target_group_id IS NOT NULL
                    ORDER BY post_id, end_at DESC
                ) AS t
                FULL OUTER JOIN ocsensor_group on (t.target_group_id = ocsensor_group.id)"
            );
        }

        if (!self::isSchemaInstalled('ocsensor_has_comment_after_close')) {
            $db->query(
                "CREATE OR REPLACE VIEW ocsensor_has_comment_after_close AS
            SELECT DISTINCT(ocsensor_current_post_status.id) as post_id, commented_at::bool as has_comment FROM ocsensor_current_post_status
                FULL OUTER JOIN ocsensor_commented_after_closed ON (ocsensor_commented_after_closed.post_id = ocsensor_current_post_status.id)
                ORDER BY ocsensor_current_post_status.id ASC"
            );
        }
    }

    public function find($requestPage = 1)
    {
        self::installSchemaIfNeeded();
        $requestPage = (int)$requestPage;
        if ($requestPage < 1){
            $requestPage = 1;
        }
        $db = eZDB::instance();
        $limit = 50;
        $offset = $limit * ($requestPage - 1);

        if (!self::isSchemaInstalled('ocsensor_criticals')) {
            $this->createView();
        }

        $count = $db->arrayQuery("SELECT count(*) FROM ocsensor_criticals")[0]['count'];
        $results = $db->arrayQuery("SELECT * FROM ocsensor_criticals LIMIT $limit OFFSET $offset");
        $page = $requestPage;
        $pages = ceil($count/$limit);
        $next = $page + 1;
        $previous = $page - 1;
        return [
            'total' => (int)$count,
            'page' => $page,
            'next' => $next > $pages ? false : $next,
            'previous' => $previous <= 0 ? false : $previous,
            'pages' => $pages,
            'limit' => $limit,
            'offset' => $offset,
            'hits' => $this->serializeResults($results),
        ];
    }

    private function serializeResults($results)
    {
        foreach ($results as $index => $result){
            $results[$index]['post_id'] = (int)$results[$index]['post_id'];
            $results[$index]['published'] = (int)$results[$index]['published'];
            $results[$index]['reassign_count'] = (int)$results[$index]['reassign_count'];
            $results[$index]['reopen_count'] = (int)$results[$index]['reopen_count'];
            $results[$index]['duration'] = floor($results[$index]['duration']/24/60/60);
            $results[$index]['has_comment_after_close'] = $results[$index]['has_comment_after_close'] === 't';
        }
        return $results;
    }

    public function getQuery()
    {
        $sql = json_decode($this->getSqlSiteData()->attribute('value'), true);
        $where = '';
        if (!empty($sql)){
            $params = [];
            foreach ($sql['params'] as $key => $value){
                if (strpos($key, 'ocsensor_current_post_status.contentobject_state_id') !== false){
                    $valueParts = explode(',', $value);
                    $value = "'" . implode("','", $valueParts) . "'";
                }
                if (strpos($key, 'duration') !== false){
                    $value = $value * 24 * 60 * 60;
                }
                $params[':' . $key] = $value;
            }
            $where = 'WHERE ' . strtr($sql['sql'], $params);
        }

        return "SELECT 
                    t.post_id, 
                    ocsensor_current_post_status.name, 
                    published, 
                    reassign_count, 
                    reopen_count, 
                    duration, 
                    ocsensor_latest_group_assignment.name AS latest_group, 
                    ocsensor_latest_group_assignment.reference AS group_reference,
                    ocsensor_has_comment_after_close.has_comment AS has_comment_after_close
                FROM (
                    SELECT post_id, 
                    count(*) FILTER (WHERE action = 'reassigning') AS \"reassign_count\",
                    count(*) FILTER (WHERE action = 'reopening') AS \"reopen_count\",
                    sum(duration) AS \"duration\" 
                    FROM ocsensor_timeline
                    GROUP BY post_id
                ) AS t 
            LEFT JOIN ocsensor_has_comment_after_close ON (ocsensor_has_comment_after_close.post_id = t.post_id) 
            LEFT JOIN ocsensor_current_post_status ON (ocsensor_current_post_status.id = t.post_id) 
            LEFT JOIN ocsensor_latest_group_assignment ON (ocsensor_latest_group_assignment.post_id = t.post_id) 
        $where ORDER BY t.post_id ASC";
    }

    private function createView()
    {
        $query = $this->getQuery();
        eZDebug::writeDebug($query, __METHOD__);

        eZDB::instance()->query(
            "CREATE OR REPLACE VIEW ocsensor_criticals AS $query"
        );
    }

    public function getFilters()
    {
        return [
            [
                'id' => 't.reassign_count',
                'label' => 'Numero di riassegnazioni',
                'type' => 'integer',
                'operators' => ['equal', 'not_equal', 'less', 'less_or_equal', 'greater', 'greater_or_equal']
            ],
            [
                'id' => 't.reopen_count',
                'label' => 'Segnalazioni riaperte',
                'type' => 'integer',
                'operators' => ['equal'],
                'input' => 'select',
                'values' => [
                    ['value' => 0, 'label' => 'No'],
                    ['value' => 1, 'label' => 'Sì'],
                ]
            ],
            [
                'id' => 'duration',
                'label' => 'Giornate di lavorazione',
                'type' => 'integer',
                'operators' => ['less', 'less_or_equal', 'greater', 'greater_or_equal']
            ],
            [
                'id' => 'ocsensor_has_comment_after_close.has_comment',
                'label' => 'Segnalazioni con una risposta successiva alla chiusura',
                'type' => 'string',
                'input' => 'select',
                'operators' => ['is_null', 'is_not_null'],
            ],
            [
                'id' => 'ocsensor_current_post_status.contentobject_state_id',
                'label' => 'Stato',
                'type' => 'string',
                'input' => 'checkbox',
                'values' => ['open', 'close'],
                'operators' => ['in']
            ],
        ];
    }

    private function getRulesSiteData()
    {
        $siteData = eZSiteData::fetchByName('sensor_criticals_rules');
        if (!$siteData instanceof eZSiteData){
            $siteData = new eZSiteData([
                'name' => 'sensor_criticals_rules',
                'value' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        [
                            'id' => 'ocsensor_current_post_status.contentobject_state_id',
                            'operator' => 'in',
                            'value' => ['open'],
                        ],
                        [
                            'condition' => 'OR',
                            'rules' => [
                                [
                                    'id' => 't.reassign_count',
                                    'operator' => 'greater_or_equal',
                                    'value' => '2',
                                ],
                                [
                                    'id' => 't.reopen_count',
                                    'operator' => 'equal',
                                    'value' => '1',
                                ],
                            ]
                        ]
                    ]
                ])
            ]);
            $siteData->store();
        }

        return $siteData;
    }

    public function getRules()
    {
        return json_decode($this->getRulesSiteData()->attribute('value'), true);
    }

    public function storeRules($rules, $sql)
    {
        $ruleSiteData = $this->getRulesSiteData();
        $ruleSiteData->setAttribute('value', json_encode($rules));
        $ruleSiteData->store();

        $sqlSiteData = $this->getSqlSiteData();
        $currentSql = $sqlSiteData->attribute('value');
        $newSql = json_encode($sql);
        $sqlSiteData->setAttribute('value', $newSql);
        $sqlSiteData->store();
        $changed = false;
        if ($currentSql != $newSql){
            $this->createView();
            $changed = true;
        }
        return $changed;
    }

    private function getSqlSiteData()
    {
        $siteData = eZSiteData::fetchByName('sensor_criticals_sql');
        if (!$siteData instanceof eZSiteData){
            $siteData = new eZSiteData([
                'name' => 'sensor_criticals_sql',
                'value' => json_encode([])
            ]);
            $siteData->store();
        }

        return $siteData;
    }

    public function getSql()
    {
        return json_decode($this->getSqlSiteData()->attribute('value'), true);
    }
}

/*

*/