<?php

use Opencontent\Sensor\Legacy\Utils;

class SensorDailyReportRepository extends OCCustomSearchableRepositoryAbstract implements OCCustomSearchableRepositoryObjectCreatorInterface
{
    use StatsPivotRepository;

    private $repository;

    private $firstDay;

    private $days;

    private $fields;

    private $categories = [];

    private $groups = [];

    public function __construct()
    {
        $this->repository = OpenPaSensorRepository::instance();
        $dateBounds = SensorOperator::getPostsDateBounds();
        $this->firstDay = $dateBounds['first'];
        $this->days = $dateBounds['first']->diff($dateBounds['last'])->days;
        $categoryTree = $this->repository->getCategoriesTree()->toArray();
        foreach ($categoryTree['children'] as $item) {
            $this->categories[$item['id']] = [
                'name' => $item['name'],
                'children' => []
            ];
            foreach ($item['children'] as $child) {
                $this->categories[$item['id']]['children'][] = $child['id'];
            }
        }
        $groupTree = $this->repository->getGroupsTree();
        $groupTagCounter = [];
        foreach ($groupTree->attribute('children') as $groupTreeItem) {
            $groupTag = $groupTreeItem->attribute('group');
            if (!empty($groupTag)) {
                if (isset($groupTagCounter[$groupTag])){
                    $groupTagId = $groupTagCounter[$groupTag];
                }else{
                    $groupTagId = $groupTagCounter[$groupTag] = count($groupTagCounter) + 1;
                }
                if (isset($this->groups[$groupTagId])){
                    $this->groups[$groupTagId]['children'][] = $groupTreeItem->attribute('id');
                }else{
                    $this->groups[$groupTagId] = [
                        'name' => '*'.$groupTag,
                        'children' => [$groupTreeItem->attribute('id')]
                    ];
                }
            }
            $this->groups[$groupTreeItem->attribute('id')] = [
                'name' => $groupTreeItem->attribute('name'),
                'children' => []
            ];
        }
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    public function getIdentifier()
    {
        return 'sensor_daily_report';
    }

    public function getFields()
    {
        if ($this->fields === null) {
            $this->fields = [];
            $this->fields[] = OCCustomSearchableField::create('date', 'date', 'Data');
            $this->fields[] = OCCustomSearchableField::create('timestamp', 'int', 'Timestamp');
            $this->fields[] = OCCustomSearchableField::create('is_done', 'boolean', 'Concluso');
            $this->fields[] = OCCustomSearchableField::create('open', 'int', 'Aperte');
            $this->fields[] = OCCustomSearchableField::create('close', 'int', 'Chiuse');
            $this->fields[] = OCCustomSearchableField::create('percentage', 'sfloat', '%');
            foreach ($this->categories as $id => $category) {
                $this->fields[] = OCCustomSearchableField::create('open_cat_' . $id, 'int', $this->cleanLabel('Aperte ' . $category['name']));
                $this->fields[] = OCCustomSearchableField::create('close_cat_' . $id, 'int', $this->cleanLabel('Chiuse ' . $category['name']));
                $this->fields[] = OCCustomSearchableField::create('percentage_cat_' . $id, 'sfloat', $this->cleanLabel('% ' . $category['name']));
            }
            foreach ($this->groups as $id => $group) {
                $this->fields[] = OCCustomSearchableField::create('open_group_' . $id, 'int', $this->cleanLabel('Aperte ' . $group['name']));
                $this->fields[] = OCCustomSearchableField::create('close_group_' . $id, 'int', $this->cleanLabel('Chiuse ' . $group['name']));
                $this->fields[] = OCCustomSearchableField::create('percentage_group_' . $id, 'sfloat', $this->cleanLabel('% ' . $group['name']));
            }
        }

        return $this->fields;
    }

    private function cleanLabel($label)
    {
        return str_replace("'", "", $label);
    }

    public function countSearchableObjects()
    {
        return $this->days;
    }

    public function fetchSearchableObjectList($limit, $offset)
    {
        $items = [];
        for ($i = 1; $i <= $limit; $i++) {
            $dayNumber = $i + $offset;
            if ($dayNumber <= $this->days) {
                $day = clone $this->firstDay;
                $day->add(new DateInterval("P{$dayNumber}D"));
                $items[] = $this->generateDailyReport($day);
            }
        }

        return $items;
    }

    private function generateDailyReport(DateTime $day)
    {
        $data = [];
        $day->setTime(23, 59, 59);
        $dayString = strftime('%Y-%m-%dT%H:%M:%SZ', $day->format('U'));
        $data['date'] = $dayString;
        $data['timestamp'] = $day->format('U');

        $today = new DateTime('now', Utils::getDateTimeZone());
        $today->setTime(23, 59, 59);
        $diff = $today->diff($day);
        $diffDays = (integer)$diff->format( "%R%a" );
        $data['is_done'] = $diffDays < 0;

        $closeQuery = "raw[sensor_close_dt] range [*,$dayString] and raw[sensor_status_lk] = 'close' facets [raw[sensor_category_id_list_lk],raw[sensor_last_owner_group_id_i]] limit 1";
        $closeSearch = $this->repository->getStatisticsService()->searchPosts($closeQuery);
        $data['close'] = (int)$closeSearch->totalCount;
        foreach ($this->categories as $catId => $category) {
            $count = 0;
            if (isset($closeSearch->facets[0]['data'][$catId])){
                $count += $closeSearch->facets[0]['data'][$catId];
            }
            foreach ($category['children'] as $childCatId){
                if (isset($closeSearch->facets[0]['data'][$childCatId])){
                    $count += $closeSearch->facets[0]['data'][$childCatId];
                }
            }
            $data['close_cat_' . $catId] = $count;
        }
        foreach ($this->groups as $id => $group) {
            $count = 0;
            if (isset($closeSearch->facets[1]['data'][$id])){
                $count += $closeSearch->facets[1]['data'][$id];
            }
            foreach ($group['children'] as $childId){
                if (isset($closeSearch->facets[1]['data'][$childId])){
                    $count += $closeSearch->facets[1]['data'][$childId];
                }
            }
            $data['close_group_' . $id] = $count;
        }
        $openQuery = "raw[sensor_open_dt] range [*,$dayString] facets [raw[sensor_category_id_list_lk],raw[sensor_last_owner_group_id_i]] limit 1";
        $openSearch = $this->repository->getStatisticsService()->searchPosts($openQuery);
        $data['open'] = (int)$openSearch->totalCount;
        foreach ($this->categories as $catId => $category) {
            $count = 0;
            if (isset($openSearch->facets[0]['data'][$catId])){
                $count += $openSearch->facets[0]['data'][$catId];
            }
            foreach ($category['children'] as $childCatId){
                if (isset($openSearch->facets[0]['data'][$childCatId])){
                    $count += $openSearch->facets[0]['data'][$childCatId];
                }
            }
            $data['open_cat_' . $catId] = $count;
        }
        foreach ($this->groups as $id => $group) {
            $count = 0;
            if (isset($openSearch->facets[1]['data'][$id])){
                $count += $openSearch->facets[1]['data'][$id];
            }
            foreach ($group['children'] as $childId){
                if (isset($openSearch->facets[1]['data'][$childId])){
                    $count += $openSearch->facets[1]['data'][$childId];
                }
            }
            $data['open_group_' . $id] = $count;
        }

        $data['percentage'] = $this->getPercentage($data['close'], $data['open']);
        foreach (array_keys($this->categories) as $catId) {
            $data['percentage_cat_' . $catId] = $this->getPercentage($data['close_cat_' . $catId], $data['open_cat_' . $catId]);
        }
        foreach (array_keys($this->groups) as $id) {
            $data['percentage_group_' . $id] = $this->getPercentage($data['close_group_' . $id], $data['open_group_' . $id]);
        }

        return new SensorDailyReport($data);
    }

    private function getPercentage($x, $y)
    {
        if ($y == 0) return 0;

        $percent = $x / $y;
        return (float)number_format($percent * 100, 2);
    }

    public function fetchSearchableObject($objectID)
    {
        try {
            $day = new DateTime($objectID, Utils::getDateTimeZone());
            $day->setTime(0, 0);
            return $this->generateDailyReport($day);

        } catch (Exception $e) {
            eZDebug::writeError($e->getMessage(), __METHOD__);
            return null;
        }
    }

    public function instanceObject($data, $guid)
    {
        return new SensorDailyReport($data);
    }

    // deprecations
    public function availableForClass()
    {
        //ignore using OCCustomSearchableRepositoryObjectCreatorInterface
        return null;
    }
}