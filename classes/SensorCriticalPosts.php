<?php

class SensorCriticalPosts
{
    private static $isSchemaInstalled;

    private static function isSchemaInstalled($tableName, $type = 'table')
    {
        if (!isset(self::$isSchemaInstalled[$tableName])) {
            $dbName = eZINI::instance()->variable('DatabaseSettings', 'Database');
            if ($type === 'table' || $type === 'view' ) {
                $res = eZDB::instance()->arrayQuery(
                    "SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_catalog = '$dbName' AND table_name   = '$tableName');"
                );
            }else{
                $res = eZDB::instance()->arrayQuery(
                    "SELECT EXISTS (SELECT FROM pg_matviews WHERE matviewname   = '$tableName');"
                );
            }
            self::$isSchemaInstalled[$tableName] = $res[0]['exists'] == 't';
        }

        return self::$isSchemaInstalled[$tableName];
    }

    private static function installSchemaIfNeeded()
    {
        $db = eZDB::instance();

        if (!self::isSchemaInstalled('ocsensor_message_count')) {
            $db->query(
                "CREATE OR REPLACE VIEW ocsensor_message_count AS
            SELECT DISTINCT ON (ezcollab_item.data_int1) ezcollab_item.data_int1 AS post_id, 
                count(DISTINCT message_id) FILTER (WHERE ezcollab_item_message_link.message_type = 0) AS \"timelines\",
                count(DISTINCT message_id) FILTER (WHERE ezcollab_item_message_link.message_type = 1) AS \"comments\",
                count(DISTINCT message_id) FILTER (WHERE ezcollab_item_message_link.message_type = 2) AS \"responses\",
                count(DISTINCT message_id) FILTER (WHERE ezcollab_item_message_link.message_type NOT IN (0,1,2,4)) AS \"private_messages\"
                FROM ezcollab_item
                  JOIN ezcollab_item_message_link ON (ezcollab_item.id = ezcollab_item_message_link.collaboration_id)                  
                GROUP BY ezcollab_item.data_int1
                ORDER BY ezcollab_item.data_int1"
            );
        }

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

        if (!self::isSchemaInstalled('ocsensor_messages')) {
            $db->query(
                "CREATE OR REPLACE VIEW ocsensor_messages AS
            SELECT DISTINCT(ocsensor_current_post_status.id) as post_id, comments, responses, private_messages, commented_at::bool as has_comment FROM ocsensor_current_post_status
                FULL OUTER JOIN ocsensor_commented_after_closed ON (ocsensor_commented_after_closed.post_id = ocsensor_current_post_status.id)
                FULL OUTER JOIN ocsensor_message_count ON (ocsensor_message_count.post_id = ocsensor_current_post_status.id)
                ORDER BY ocsensor_current_post_status.id ASC"
            );
        }
    }

    public function find($requestPage = 1, $latestGroups = [], $references = [])
    {
        self::installSchemaIfNeeded();
        $requestPage = (int)$requestPage;
        if ($requestPage < 1){
            $requestPage = 1;
        }
        $db = eZDB::instance();
        $limit = 50;
        $offset = $limit * ($requestPage - 1);

        if (!self::isSchemaInstalled('ocsensor_criticals', 'materialized_view')) {
            $this->createView();
        }

        $where = '';
        $whereParts = [];
        if (!is_array($latestGroups)){
            $latestGroups = [$latestGroups];
        }
        if (count($latestGroups) > 0){
            $cleanGroups = [];
            foreach ($latestGroups as $latestGroup){
                if (!empty($latestGroup)) {
                    $cleanGroups[] = "'" . $db->escapeString($latestGroup) . "'";
                }
            }
            if (!empty($cleanGroups)) {
                $whereParts[] = 'latest_group IN (' . implode(',', $cleanGroups) . ')';
            }
        }
        if (!is_array($references)){
            $references = [$references];
        }
        if (count($references) > 0){
            $cleanReferences = [];
            foreach ($references as $reference){
                if (!empty($reference)) {
                    $cleanReferences[] = "'" . $db->escapeString($reference) . "'";
                }
            }
            if (!empty($cleanReferences)) {
                $whereParts[] = 'group_reference IN (' . implode(',', $cleanReferences) . ')';
            }
        }
        if (count($whereParts)){
            $where = 'WHERE ' . implode(' OR ', $whereParts);
        }
        $countUnfiltered = $db->arrayQuery("SELECT count(*) FROM ocsensor_criticals")[0]['count'];
        $count = $db->arrayQuery("SELECT count(*) FROM ocsensor_criticals $where")[0]['count'];
        $results = $db->arrayQuery("SELECT * FROM ocsensor_criticals $where LIMIT $limit OFFSET $offset");
        $groupFacets = $db->arrayQuery('SELECT DISTINCT(latest_group) FROM ocsensor_criticals ORDER BY latest_group;');
        $referenceFacets = $db->arrayQuery('SELECT DISTINCT(group_reference) FROM ocsensor_criticals ORDER BY group_reference;');
        $resultGroups = [];
        foreach ($groupFacets as $group){
            if (!empty($group['latest_group'])) {
                $resultGroups[] = [
                    'name' => $group['latest_group'],
                    'selected' => in_array($group['latest_group'], $latestGroups),
                ];
            }
        }
        $resultReferences = [];
        foreach ($referenceFacets as $referenceFacet){
            if (!empty($referenceFacet['group_reference'])) {
                $resultReferences[] = [
                    'name' => $referenceFacet['group_reference'],
                    'selected' => in_array($referenceFacet['group_reference'], $references),
                ];
            }
        }
        $page = $requestPage;
        $pages = ceil($count/$limit);
        $next = $page + 1;
        $previous = $page - 1;
        return [
            'total' => (int)$count,
            'total_unfiltered' => (int)$countUnfiltered,
            'page' => $page,
            'next' => $next > $pages ? false : $next,
            'previous' => $previous <= 0 ? false : $previous,
            'pages' => $pages,
            'limit' => $limit,
            'offset' => $offset,
            'hits' => $this->serializeResults($results),
            'groups' => $resultGroups,
            'references' => $resultReferences,
        ];
    }

    public function findAll()
    {
        $db = eZDB::instance();
        $results = $db->arrayQuery("SELECT * FROM ocsensor_criticals");

        return $this->serializeResults($results);
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
            $results[$index]['comment_count'] = (int)$results[$index]['comment_count'];
            $results[$index]['private_message_count'] = (int)$results[$index]['private_message_count'];
        }
        return $results;
    }

    public function getQuery()
    {
        $sql = $this->getSql();
        $where = '';
        if (!empty($sql)){
            $params = [];
            foreach ($sql['params'] as $key => $value){
                $key = str_replace('ocsensor_has_comment_after_close', 'ocsensor_messages', $key); //bc
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
                    ocsensor_messages.comments AS comment_count,
                    ocsensor_messages.private_messages AS private_message_count,
                    ocsensor_messages.has_comment AS has_comment_after_close
                FROM (
                    SELECT post_id, 
                    count(*) FILTER (WHERE action = 'reassigning') AS \"reassign_count\",
                    count(*) FILTER (WHERE action = 'reopening') AS \"reopen_count\",
                    sum(duration) AS \"duration\" 
                    FROM ocsensor_timeline
                    GROUP BY post_id
                ) AS t 
            LEFT JOIN ocsensor_messages ON (ocsensor_messages.post_id = t.post_id) 
            LEFT JOIN ocsensor_current_post_status ON (ocsensor_current_post_status.id = t.post_id) 
            LEFT JOIN ocsensor_latest_group_assignment ON (ocsensor_latest_group_assignment.post_id = t.post_id) 
        $where ORDER BY t.post_id ASC";
    }

    private function createView()
    {
        $query = $this->getQuery();
        eZDebug::writeDebug('Replace criticals view', __METHOD__);
        $db = eZDB::instance();
        $db->query('DROP MATERIALIZED VIEW IF EXISTS ocsensor_criticals');
        $db->query("CREATE MATERIALIZED VIEW IF NOT EXISTS ocsensor_criticals AS $query");
        $db->query('CREATE UNIQUE INDEX IF NOT EXISTS ocsensor_criticals_idx ON ocsensor_criticals (post_id);');
    }

    public function updateView()
    {
        $db = eZDB::instance();
        $db->query('REFRESH MATERIALIZED VIEW CONCURRENTLY ocsensor_criticals');
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
                'id' => 'ocsensor_messages.has_comment',
                'label' => 'Segnalazioni con una risposta successiva alla chiusura',
                'type' => 'string',
                'input' => 'select',
                'operators' => ['is_null', 'is_not_null'],
            ],
            [
                'id' => 'ocsensor_messages.comments',
                'label' => 'Numero di commenti',
                'type' => 'integer',
                'operators' => ['equal', 'not_equal', 'less', 'less_or_equal', 'greater', 'greater_or_equal']
            ],
            [
                'id' => 'ocsensor_messages.private_messages',
                'label' => 'Numero di note private',
                'type' => 'integer',
                'operators' => ['equal', 'not_equal', 'less', 'less_or_equal', 'greater', 'greater_or_equal']
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
                    'current' => 'default',
                    'presets' => [
                        'default' => [
                            'name' => 'default',
                            'rules' => $this->getDefaultRules(),
                        ],
                    ],
                ]),
            ]);
            $siteData->store();
        }

        return $siteData;
    }

    private function getDefaultRules()
    {
        return [
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
                    ],
                ],
            ],
        ];
    }

    public function resetRulesAndQuery()
    {
        $this->getRulesSiteData()->remove();
        $this->getSqlSiteData()->remove();
    }

    public function getRules()
    {
        $rules = json_decode($this->getRulesSiteData()->attribute('value'), true);
        $presets = $rules['presets'];
        $current = $rules['current'];
        foreach ($presets as $preset){
            if ($preset['name'] == $current){
                return $preset['rules'];
            }
        }

        return $this->getDefaultRules();
    }

    public function getAllRulesAndSql()
    {
        return [
            'rules' => json_decode($this->getRulesSiteData()->attribute('value'), true),
            'sql' => json_decode($this->getSqlSiteData()->attribute('value'), true),
        ];
    }

    public function getCurrentPreset()
    {
        $rules = json_decode($this->getRulesSiteData()->attribute('value'), true);
        return $rules['current'];
    }

    public function getPresets()
    {
        $rules = json_decode($this->getRulesSiteData()->attribute('value'), true);
        $presets = [];
        foreach ($rules['presets'] as $preset){
            $presets[] = $preset['name'];
        }

        return $presets;
    }

    public function storeRules($rules, $sql, $presetName = 'default')
    {
        $presetName = empty($presetName) ? 'default' : $presetName;

        $ruleSiteData = $this->getRulesSiteData();
        $newRules = json_decode($ruleSiteData->attribute('value'), true);
        $newRules['current'] = $presetName;
        $newRules['presets'][$presetName] = [
            'name' => $presetName,
            'rules' => $rules,
        ];
        $ruleSiteData->setAttribute('value', json_encode($newRules));
        $ruleSiteData->store();

        $sqlSiteData = $this->getSqlSiteData();
        $sqls = json_decode($sqlSiteData->attribute('value'), true);
        $sqls['current'] = $presetName;
        $sqls['presets'][$presetName] = [
            'name' => $presetName,
            'sql' => $sql,
        ];
        $sqlSiteData->setAttribute('value', json_encode($sqls));
        $sqlSiteData->store();
        $this->createView();
        return true;
    }

    private function getSqlSiteData()
    {
        $siteData = eZSiteData::fetchByName('sensor_criticals_sql');
        if (!$siteData instanceof eZSiteData){
            $siteData = new eZSiteData([
                'name' => 'sensor_criticals_sql',
                'value' => json_encode([
                    'current' => null,
                    'presets' => [],
                ]),
            ]);
            $siteData->store();
        }

        return $siteData;
    }

    public function getSql()
    {
        $sqls = json_decode($this->getSqlSiteData()->attribute('value'), true);
        $presets = $sqls['presets'];
        $current = $sqls['current'];
        foreach ($presets as $preset){
            if ($preset['name'] == $current){
                return $preset['sql'];
            }
        }

        return [];
    }

    public function setPreset($presetName)
    {
        $presets = $this->getPresets();
        if (in_array($presetName, $presets)){
            $ruleSiteData = $this->getRulesSiteData();
            $newRules = json_decode($ruleSiteData->attribute('value'), true);
            $newRules['current'] = $presetName;
            $ruleSiteData->setAttribute('value', json_encode($newRules));
            $ruleSiteData->store();
            $sqlSiteData = $this->getSqlSiteData();
            $sqls = json_decode($sqlSiteData->attribute('value'), true);
            $sqls['current'] = $presetName;
            $sqlSiteData->setAttribute('value', json_encode($sqls));
            $sqlSiteData->store();
            $this->createView();
        }
    }

    public function removePreset($presetName)
    {
        $presets = $this->getPresets();
        $current = $this->getCurrentPreset();
        if (in_array($presetName, $presets) && $current !== $presetName){
            $ruleSiteData = $this->getRulesSiteData();
            $newRules = json_decode($ruleSiteData->attribute('value'), true);
            unset($newRules['presets'][$presetName]);
            $ruleSiteData->setAttribute('value', json_encode($newRules));
            $ruleSiteData->store();
            $sqlSiteData = $this->getSqlSiteData();
            $sqls = json_decode($sqlSiteData->attribute('value'), true);
            unset($sqls['presets'][$presetName]);
            $sqlSiteData->setAttribute('value', json_encode($sqls));
            $sqlSiteData->store();

            return true;
        }

        return false;
    }
}

/*

*/