<?php

use League\Event\AbstractListener;
use League\Event\EventInterface;
use Opencontent\Sensor\Api\Values\Event as SensorEvent;
use Opencontent\Sensor\Api\Values\ParticipantRole;
use Opencontent\Sensor\Legacy\OperatorService;
use Opencontent\Sensor\Legacy\SearchService\SolrMapper;

class SensorReindexer extends AbstractListener
{
    private static $operatorsByGroup = [];

    public static function reindexPostsByGroupId($groupId, $chunkLength = 500)
    {
        $solr = new eZSolr();
        $db = eZDB::instance();
        $updateData = [];
        $repository = OpenPaSensorRepository::instance();
        $groupId = (int)$groupId;
        if ($groupId > 0) {
            //$repository->getLogger()->debug('Start reindex post participants by group #' . $groupId);
            try {
                $rows = $db->arrayQuery("select ci.data_int1 as id, ci.id as internal_id
                    from ezcollab_item ci 
                      inner join ezcollab_item_participant_link cipl 
                      on ci.id = cipl.collaboration_id  
                    where cipl.participant_id = $groupId and cipl.participant_type = 2 and cipl.participant_role in (2,3,4)");

                foreach ($rows as $row) {
                    /** @var eZCollaborationItemParticipantLink[] $participantLinks */
                    $participantLinks = eZCollaborationItemParticipantLink::fetchParticipantList([
                        'item_id' => (int)$row['internal_id'],
                        'limit' => 1000 // avoid ez cache
                    ]);

                    $participantIdList = [];
                    $approverIdList = [];
                    $ownerIdList = [];
                    $ownerGroupIdList = [];
                    $ownerUserIdList = [];
                    $observerIdList = [];
                    foreach ($participantLinks as $participantLink) {
                        $role = $participantLink->attribute('participant_role');
                        if ($participantLink->attribute('participant_type') == eZCollaborationItemParticipantLink::TYPE_USER) {
                            $participantIdList[] = $participantLink->attribute('participant_id');
                            if ($role == ParticipantRole::ROLE_APPROVER) {
                                $approverIdList[] = $participantLink->attribute('participant_id');
                            }
                            if ($role == ParticipantRole::ROLE_OBSERVER) {
                                $observerIdList[] = $participantLink->attribute('participant_id');
                            }
                            if ($role == ParticipantRole::ROLE_OWNER) {
                                $ownerIdList[] = $participantLink->attribute('participant_id');
                                $ownerUserIdList[] = $participantLink->attribute('participant_id');
                            }
                        } elseif ($participantLink->attribute('participant_type') == eZCollaborationItemParticipantLink::TYPE_USERGROUP) {
                            $operatorsIdList = self::getOperatorsIdByGroupId((int)$participantLink->attribute('participant_id'));
                            $participantIdList = array_merge($participantIdList, $operatorsIdList);
                            if ($role == ParticipantRole::ROLE_APPROVER) {
                                $approverIdList = array_merge($approverIdList, $operatorsIdList);
                            }
                            if ($role == ParticipantRole::ROLE_OBSERVER) {
                                $observerIdList = array_merge($observerIdList, $operatorsIdList);
                            }
                            if ($role == ParticipantRole::ROLE_OWNER) {
                                $ownerIdList = array_merge($ownerIdList, $operatorsIdList);
                                $ownerGroupIdList = array_merge($ownerGroupIdList, $operatorsIdList);
                            }
                        }
                    }

                    $updateData[] = [
                        'meta_guid_ms' => $solr->guid((int)$row['id'], eZLocale::currentLocaleCode()),
                        'sensor_participant_id_list_lk' => [
                            'set' => implode(',', array_unique($participantIdList)),
                        ],
                        'sensor_approver_id_list_lk' => [
                            'set' => implode(',', array_unique($approverIdList)),
                        ],
                        'sensor_owner_id_list_lk' => [
                            'set' => implode(',', array_unique($ownerIdList)),
                        ],
                        'sensor_owner_user_id_list_lk' => [
                            'set' => implode(',', array_unique($ownerUserIdList)),
                        ],
                        'sensor_owner_group_id_list_lk' => [
                            'set' => implode(',', array_unique($ownerGroupIdList)),
                        ],
                        'sensor_observer_id_list_lk' => [
                            'set' => implode(',', array_unique($observerIdList)),
                        ],
                    ];
                }
            } catch (Exception $e) {
                $repository->getLogger()->error($e->getMessage());
            }
            if (!empty($updateData)) {
                try {
                    $chunks = array_chunk($updateData, $chunkLength);
                    $total = count($updateData);
                    $part = 0;
                    foreach ($chunks as $chunk) {
                        $part += count($chunk);
                        $repository->getLogger()->debug("Update post participants by group #{$groupId}: {$part}/{$total}");
                        SolrMapper::patchSearchIndex(json_encode($chunk));
                    }
                } catch (Exception $e) {
                    $repository->getLogger()->error($e->getMessage());
                }
            }
            //$repository->getLogger()->debug('End reindex post participants by group #' . $groupId);
        }
    }

    private static function getOperatorsIdByGroupId($groupId)
    {
        if (!isset(self::$operatorsByGroup[$groupId])) {
            self::$operatorsByGroup[$groupId] = [];
            $groupObject = eZContentObject::fetch($groupId);
            if ($groupObject instanceof eZContentObject) {
                $attributeId = eZContentObjectTreeNode::classAttributeIDByIdentifier('sensor_operator/struttura_di_competenza');
                self::$operatorsByGroup[$groupId] = array_column((array)$groupObject->reverseRelatedObjectList(
                    false, $attributeId, false, ['AsObject' => false]
                ), 'id');
            }

        }
        return self::$operatorsByGroup[$groupId];
    }

    public function handle(EventInterface $event, $param = null)
    {
        if ($param instanceof SensorEvent) {
            eZContentObject::clearCache([$param->user->id]);
            $object = eZContentObject::fetch((int)$param->user->id);
            if ($object instanceof eZContentObject) {
                if ($param->identifier == 'on_update_operator') {
                    $previousVersionNumber = $object->attribute('current_version') - 1;
                    $previousVersion = false;
                    while (!$previousVersion) {
                        $previousVersion = eZContentObjectVersion::fetchVersion($previousVersionNumber, $object->attribute('id'));
                        $previousVersionNumber--;
                        if ($previousVersionNumber == 0) {
                            break;
                        }
                    }
                    if ($previousVersion instanceof eZContentObjectVersion) {
                        $currentVersion = $object->currentVersion();
                        $currentVersionDataMap = $currentVersion->dataMap();
                        $groupIdentifier = OperatorService::GROUP_ATTRIBUTE_IDENTIFIER;
                        $currentVersionGroupValue = $previousVersionGroupValue = [];
                        if (isset($currentVersionDataMap[$groupIdentifier])) {
                            $currentVersionGroupValue = explode('-', $currentVersionDataMap[$groupIdentifier]->toString());
                        }
                        $previousVersionDataMap = $previousVersion->dataMap();
                        if (isset($previousVersionDataMap[$groupIdentifier])) {
                            $previousVersionGroupValue = explode('-', $previousVersionDataMap[$groupIdentifier]->toString());
                        }
                        $addGroups = array_diff($currentVersionGroupValue, $previousVersionGroupValue);
                        $removeGroups = array_diff($previousVersionGroupValue, $currentVersionGroupValue);
                        $touchedGroups = array_unique(array_merge($addGroups, $removeGroups));
                        if (!empty($touchedGroups)) {
                            SensorBatchOperations::instance()->addPendingOperation(SensorPostGroupParticipantHandler::SENSOR_HANDLER_IDENTIFIER, [
                                'groups' => implode('-', $touchedGroups),
                            ])->run();
                        }
                    }
                }elseif ($param->identifier == 'on_new_operator') {
                    $dataMap = $object->dataMap();
                    $groupIdentifier = OperatorService::GROUP_ATTRIBUTE_IDENTIFIER;
                    if (isset($dataMap[$groupIdentifier]) && $dataMap[$groupIdentifier]->hasContent()){
                        SensorBatchOperations::instance()->addPendingOperation(SensorPostGroupParticipantHandler::SENSOR_HANDLER_IDENTIFIER, [
                            'groups' => $dataMap[$groupIdentifier]->toString(),
                        ])->run();
                    }
                }
            }
        }
    }
}
