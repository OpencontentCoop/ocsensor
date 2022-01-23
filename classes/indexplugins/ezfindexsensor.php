<?php

use Opencontent\Sensor\Api\Values\Group;
use Opencontent\Sensor\Api\Values\Post;
use Opencontent\Sensor\Api\Values\Post\Field\Category;
use Opencontent\Sensor\Legacy\PostService\PostBuilder;
use Opencontent\Sensor\Legacy\PostService;
use Opencontent\Sensor\Legacy\SearchService\SolrMapper;

class ezfIndexSensor implements ezfIndexPlugin
{
    /**
     * @param eZContentObject $contentObject
     * @param eZSolrDoc[] $docList
     */
    public function modify(eZContentObject $contentObject, &$docList)
    {
        if (class_exists('OpenPaSensorRepository')) {
            $repository = OpenPaSensorRepository::instance();

            /** @var eZContentObjectVersion $version */
            $version = $contentObject->currentVersion();
            if ($version !== false) {
                $collaboration = eZPersistentObject::fetchObject(
                    eZCollaborationItem::definition(),
                    null,
                    array(
                        'data_int1' => intval($contentObject->attribute('id'))
                    )
                );
                if ($collaboration instanceof eZCollaborationItem) {
                    $availableLanguages = $version->translationList(false, false);
                    foreach ($availableLanguages as $languageCode) {
                        $repository->setCurrentLanguage($languageCode);
                        try {

                            $collaborationItem = eZPersistentObject::fetchObject(
                                eZCollaborationItem::definition(),
                                null,
                                array(
                                    'type_identifier' => $repository->getSensorCollaborationHandlerTypeString(),
                                    PostService::COLLABORATION_FIELD_OBJECT_ID => intval($contentObject->attribute('id'))
                                )
                            );
                            if ($collaborationItem instanceof eZCollaborationItem) {
                                $builder = new PostBuilder($repository, $contentObject, $collaborationItem);
                                $post = $builder->build();
                                $mapper = new SolrMapper($repository, $post);
                                $mapIndex = $mapper->mapToIndex();
                                foreach ($mapIndex as $key => $value) {
                                    $this->addField($docList[$languageCode], $key, $value);
                                }
                                SensorStatisticCollector::instance()->collect(
                                    self::createSensorStatisticPost($post, $mapIndex)
                                );
                            }
                        } catch (Exception $e) {
                            eZDebug::writeError($e->getMessage(), __METHOD__);
                        }
                    }
                }
            }
        }
    }

    public static function indexStatisticPost(eZContentObject $object)
    {
        try {
            $repository = OpenPaSensorRepository::instance();
            $post = $repository->getPostService()->loadPost($object->attribute('id'));
            $mapper = new SolrMapper($repository, $post);
            $mapIndex = $mapper->mapToIndex();

            SensorStatisticCollector::instance()->collect(
                self::createSensorStatisticPost($post, $mapIndex)
            );
        }catch (Exception $e){

        }
    }

    private static function createSensorStatisticPost(Post $post, $mapIndex)
    {
        $repository = OpenPaSensorRepository::instance();
        $categories = $post->categories;
        $macroCategory = $microCategory = null;
        if (!empty($categories)){
            $category = $categories[0];
            if ($category->parent === 0){
                $macroCategory = $microCategory = $category->name;
            }else{
                $macroCategoryObject = $repository->getCategoryService()->loadCategory($category->parent);
                if ($macroCategoryObject instanceof Category){
                    $macroCategory = $macroCategoryObject->name;
                }else {
                    $macroCategory = $category->parent;
                }
                $microCategory = $category->name;
            }
        }

        $group = null;
        $groupId = @isset($mapIndex['sensor_last_owner_group_id_i']) ? (int)$mapIndex['sensor_last_owner_group_id_i'] : null;
        if ($groupId){
            $groupObject = $repository->getGroupService()->loadGroup($groupId, []);
            if ($groupObject instanceof Group){
                $group = $groupObject->name;
            }
        }

        $statisticPost = new SensorStatisticPost();
        $statisticPost->id = (int)$mapIndex['sensor_post_id_si'];
        $statisticPost->type = @$mapIndex['sensor_type_s'];
        $statisticPost->status = @$mapIndex['sensor_status_lk'];
        $statisticPost->workflow = @$mapIndex['sensor_workflow_status_lk'];
        $statisticPost->privacy = @$mapIndex['sensor_privacy_lk'];
        $statisticPost->moderation = @$mapIndex['sensor_moderation_lk'];
        $statisticPost->coordinates = @str_replace(',', ' ', $mapIndex['sensor_coordinates_gpt']);
        $statisticPost->expire_at = @$mapIndex['sensor_expiration_dt'];
        $statisticPost->behalf = @$mapIndex['sensor_behalf_b'];
        $statisticPost->is_read = @$mapIndex['sensor_is_read_i'];
        $statisticPost->is_assigned = @$mapIndex['sensor_is_assigned_i'];
        $statisticPost->is_fixed = @$mapIndex['sensor_is_fixed_i'];
        $statisticPost->is_closed = @$mapIndex['sensor_is_closed_i'];
        $statisticPost->open_at = @$mapIndex['sensor_open_dt'];
        $statisticPost->read_at = @$mapIndex['sensor_read_dt'];
        $statisticPost->assigned_at = @@$mapIndex['sensor_assigned_dt'];
        $statisticPost->fixed_at = @@$mapIndex['sensor_fix_dt'];
        $statisticPost->closed_at = @@$mapIndex['sensor_close_dt'];
        $statisticPost->owner_group = $group;
        $statisticPost->area = @$mapIndex['sensor_area_name_list_lk'];
        $statisticPost->category = $macroCategory;
        $statisticPost->category_detail = $microCategory;
        $statisticPost->reading_duration = @$mapIndex['sensor_reading_time_i'];
        $statisticPost->assigning_duration = @$mapIndex['sensor_read_assign_time_i'];
        $statisticPost->fixing_duration = @$mapIndex['sensor_fixing_time_i'];
        $statisticPost->closing_duration = @$mapIndex['sensor_closing_time_i'];
        $statisticPost->bouncing_ball_duration = @$mapIndex['sensor_bouncing_ball_time_i'];

        return $statisticPost;
    }

    protected function addField(eZSolrDoc $doc, $fieldName, $fieldValue)
    {
        if ($doc instanceof eZSolrDoc) {
            if ($doc->Doc instanceof DOMDocument) {
                $xpath = new DomXpath($doc->Doc);
                if ($xpath->evaluate('//field[@name="' . $fieldName . '"]')->length == 0) {
                    $doc->addField($fieldName, $fieldValue);
                }
            } elseif (is_array($doc->Doc) && !isset($doc->Doc[$fieldName])) {
                $doc->addField($fieldName, $fieldValue);
            }
        }
    }
}
