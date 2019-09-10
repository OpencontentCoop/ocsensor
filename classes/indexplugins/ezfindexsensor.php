<?php

use Opencontent\Sensor\Legacy\PostService\PostBuilder;
use Opencontent\Sensor\Legacy\PostService;

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
                                $mapper = new \Opencontent\Sensor\Legacy\SearchService\SolrMapper($repository, $post);
                                foreach ($mapper->mapToIndex() as $key => $value) {
                                    $this->addField($docList[$languageCode], $key, $value);
                                }
                            }
                        } catch (Exception $e) {
                            eZDebug::writeError($e->getMessage(), __METHOD__);
                        }
                    }
                }
            }
        }
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
