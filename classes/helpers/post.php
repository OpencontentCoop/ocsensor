<?php

class SensorPost
{
    const STATUS_WAITING = 0;

    const STATUS_READ = 1;

    const STATUS_ASSIGNED = 2;

    const STATUS_CLOSED = 3;

    const STATUS_FIXED = 4;

    const STATUS_REOPENED = 6;

    const COLLABORATION_FIELD_OBJECT_ID = 'data_int1';

    const COLLABORATION_FIELD_LAST_CHANGE = 'data_int2';

    const COLLABORATION_FIELD_STATUS = 'data_int3';

    const COLLABORATION_FIELD_HANDLER = 'data_text1';

    const COLLABORATION_FIELD_EXPIRY = 'data_text3';

    const SITE_DATA_FIELD_PREFIX = 'sensorpost_';

    /**
     * @var eZCollaborationItem
     */
    protected $collaborationItem;

    /**
     * @var eZCollaborationItemParticipantLink[]
     */
    protected $participantList;

    /**
     * @var array
     */
    public $configParameters;

    /**
     * @var SensorPostEventHelper
     */
    public $eventHelper;

    /**
     * @var SensorPostTimelineHelper
     */
    public $timelineHelper;

    /**
     * @var SensorPostCommentHelper
     */
    public $commentHelper;

    /**
     * @var SensorPostMessageHelper
     */
    public $messageHelper;

    /**
     * @var SensorPostResponseHelper
     */
    public $responseHelper;

    /**
     * @var SensorPostObjectHelperInterface
     */
    public $objectHelper;

    protected function __construct( eZCollaborationItem $collaborationItem, SensorPostObjectHelperInterface $objectHelper, array $configParameters )
    {
        $this->collaborationItem = $collaborationItem;
        $this->configParameters = $configParameters;
        $this->eventHelper = SensorPostEventHelper::instance( $this );
        $this->timelineHelper = SensorPostTimelineHelper::instance( $this );
        $this->commentHelper = SensorPostCommentHelper::instance( $this );
        $this->messageHelper = SensorPostMessageHelper::instance( $this );
        $this->responseHelper = SensorPostResponseHelper::instance( $this );
        $this->objectHelper = $objectHelper;
    }

    final public static function instance( eZCollaborationItem $collaborationItem, SensorPostObjectHelperInterface $objectHelper, $configParameters = array() )
    {
        return new SensorPost( $collaborationItem, $objectHelper, $configParameters );
    }

    public function restoreFormTrash()
    {
        $participants = $this->getParticipants();
        foreach( $participants as $participantID )
        {
            $this->restoreParticipant( $participantID );
        }
    }

    public function moveToTrash()
    {
        $participants = $this->getParticipants();
        foreach( $participants as $participantID )
        {
            $this->trashParticipant( $participantID );
        }
    }

    public function getCollaborationItem()
    {
        return $this->collaborationItem;
    }

    public function getCurrentStatus()
    {
        return $this->collaborationItem->attribute( self::COLLABORATION_FIELD_STATUS );
    }

    public function isWaiting()
    {
        return $this->is( SensorPost::STATUS_WAITING );
    }

    public function isRead()
    {
        return $this->is( SensorPost::STATUS_READ );
    }

    public function isAssigned()
    {
        return $this->is( SensorPost::STATUS_ASSIGNED );
    }

    public function isClosed()
    {
        return $this->is( SensorPost::STATUS_CLOSED );
    }

    public function isFixed()
    {
        return $this->is( SensorPost::STATUS_FIXED );
    }

    public function isReopened()
    {
        return $this->is( SensorPost::STATUS_REOPENED );
    }

    /**
     * @param null|int $byRole
     * @param bool $asObject
     *
     * @return int[]|eZContentObject[]
     */
    public function getParticipants( $byRole = null, $asObject = false )
    {
        if ( $this->participantList === null )
        {
            $this->participantList = eZCollaborationItemParticipantLink::fetchParticipantList(
                array(
                    'item_id' => $this->collaborationItem->attribute( 'id' ),
                    'limit' => 100
                )
            );
        }
        /** @var eZCollaborationItemParticipantLink[] $participants */
        $participants = array();
        foreach( $this->participantList as $participant )
        {
            if ( $byRole !== null )
            {
                if ( $byRole == $participant->attribute( 'participant_role' ) )
                {
                    $participants[$participant->attribute( 'participant_id' )] = $participant;
                }
            }
            else
            {
                $participants[$participant->attribute( 'participant_id' )] = $participant;
            }
        }
        if ( $asObject )
        {
            $map = array();
            foreach( $participants as $id => $participant )
            {
                $sortKey = $this->participantRoleSortKey( $participant->attribute( 'participant_role' ) );
                if ( !isset( $map[$sortKey] ) )
                {
                    $sortName = self::participantRoleName( $participant->attribute( 'participant_role' ) );
                    $map[$sortKey] = array(
                        'role_name' => $sortName,
                        'role_id' => $participant->attribute( 'participant_role' ),
                        'items' => array()
                    );
                }
                $map[$sortKey]['items'][] = array(
                    'participant_link' => $participant,
                    'id' => $id,
                    'contentobject' => eZContentObject::fetch( $id )
                );
            }
            ksort( $map );
            return $map;
        }
        else
        {
            return array_keys( $participants );
        }
    }

    public function hasOwner()
    {
        return count( $this->getParticipants( SensorUserPostRoles::ROLE_OWNER ) ) > 0;
    }

    public function getMainOwner( $asObject = false )
    {
        if ( $this->hasOwner() )
        {
            $ownerIds = $this->getParticipants( SensorUserPostRoles::ROLE_OWNER );
            $ownerId = array_shift( $ownerIds );
            if ( $asObject )
            {
                return eZContentObject::fetch( $ownerId );
            }
            return $ownerId;
        }
        return null;
    }

    public function getMainOwnerText()
    {
        $text = '';
        $mainOwner = $this->getMainOwner( true );
        if ( $mainOwner instanceof eZContentObject )
        {
            $tpl = eZTemplate::factory();
            $tpl->setVariable( 'sensor_person', $mainOwner );
            $text = $tpl->fetch( 'design:content/view/sensor_person.tpl' );
        }
        return $text;
    }

    public function getCurrentParticipant()
    {
        return eZCollaborationItemParticipantLink::fetch( $this->collaborationItem->attribute( 'id' ), eZUser::currentUserID() );
    }

    public function getMainOwnerName()
    {
        $name = false;
        $mainOwner = $this->getMainOwner( true );
        if ( $mainOwner instanceof eZContentObject )
        {
            $name = $mainOwner->attribute( 'name' );
        }
        return $name;
    }

    public function getOwners( $asObject = false )
    {
        if ( $this->hasOwner() )
        {
            $ownerIds = $this->getParticipants( SensorUserPostRoles::ROLE_OWNER );
            if ( $asObject )
            {
                return eZContentObject::fetchIDArray( $ownerIds );
            }
            return $ownerIds;
        }
        return array();
    }

    public function getOwnerNames()
    {
        $names = array();
        $owners = $this->getOwners( true );
        foreach( $owners as $owner )
        {
            $names[] = $owner->attribute( 'name' );
        }
        return $names;
    }

    public function getExpiringDate()
    {
        $data = array(
            'text' => null,
            'timestamp' => null,
            'label' => 'default'
        );
        try
        {
            $date = new DateTime();
            $expiryTimestamp = intval( $this->collaborationItem->attribute( self::COLLABORATION_FIELD_EXPIRY ) );
            if ( $expiryTimestamp <= 15 ) //bc compat
            {
                $expiryTimestamp = self::expiryTimestamp( $this->collaborationItem->attribute( 'created' ) );
            }
            $date->setTimestamp( $expiryTimestamp );
            if ( $date instanceof DateTime )
            {
                $data['timestamp'] = $date->format( 'U' );
                $diff = self::getDateDiff( $date );
                /** @var DateInterval $interval */
                $interval = $diff['interval'];
                $format = $diff['format'];
                $text = ezpI18n::tr( 'sensor/expiring', 'Scade fra' );
                if ( $interval->invert )
                {
                    $text = ezpI18n::tr( 'sensor/expiring', 'Scaduto da' );
                    $data['label'] = 'danger';
                }
                $data['text'] = $text . ' ' . $interval->format( $format );
            }
            else
            {
                throw new Exception( "Invalid creation date in collaboration item" );
            }
        }
        catch( Exception $e )
        {
            $data['text'] = $e->getMessage();
        }
        return $data;
    }

    public function getResolutionTime()
    {
        $data = array(
            'text' => null,
            'timestamp' => null
        );
        if ( $this->isClosed() )
        {
            $response = $this->getLastTimelineMessage();
            if ( $response )
            {
                $start = new DateTime();
                $start->setTimestamp( $this->collaborationItem->attribute( "created" ) );
                $end = new DateTime();
                $end->setTimestamp( $response['message_link']->attribute( "created" ) );
                if ( $start instanceof DateTime )
                {
                    $diff = self::getDateDiff( $start, $end );
                    $interval = $diff['interval'];
                    $format = $diff['format'];
                    if ( $interval instanceof DateInterval )
                    {
                        $data['text'] = $interval->format( $format );
                    }
                    $data['timestamp'] = $end->format( 'U' );
                }
            }
        }
        return $data;
    }

    public function getLastTimelineMessage()
    {
        $response = null;
        $responses = $this->timelineHelper->items();
        if ( count( $responses ) >= 1 )
        {
            $response = array_pop( $responses );
        }
        return $response;
    }


    public function getExpirationDays()
    {
        $expiryTimestamp = intval( $this->collaborationItem->attribute( self::COLLABORATION_FIELD_EXPIRY ) );
        if ( $expiryTimestamp <= 15 ) //bc compat
        {
            return $this->configParameters['DefaultPostExpirationDaysInterval'];
        }
        else
        {
            $start = new DateTime();
            $start->setTimestamp( $this->collaborationItem->attribute( 'created' ) );
            $end = new DateTime();
            $end->setTimestamp( $expiryTimestamp );
            $diff = $end->diff( $start );
            if ( $diff instanceof DateInterval )
            {
                return $diff->days;
            }
        }
        return -1;
    }

    public function deactivateParticipants()
    {
        foreach( $this->getParticipants() as $id )
        {
            $this->collaborationItem->setIsActive( false, $id );
        }
    }

    public function activateParticipants()
    {
        foreach( $this->getParticipants() as $id )
        {
            $this->collaborationItem->setIsActive( true, $id );
        }
    }

    public function storeActivesParticipants()
    {
        $activeParticipants = $this->getActivesParticipants();
        $content = $this->collaborationItem->content();

        $name = self::SITE_DATA_FIELD_PREFIX . $content['content_object_id'];
        $siteData = eZSiteData::fetchByName( $name );

        $removeIfNeeded = false;

        if ( !$siteData instanceof eZSiteData)
        {
            $row = array(
                'name' => $name,
                'value' => serialize( array() )
            );
            $siteData = new eZSiteData( $row );
            $currentActiveParticipants = array();
        }
        else
        {
            $currentActiveParticipants = unserialize( $siteData->attribute( 'value' ) );
            $removeIfNeeded = true;
        }

        if ( count( $activeParticipants ) > 0 )
        {
            if ( serialize( $currentActiveParticipants ) != serialize( $activeParticipants ) )
            {
                $siteData->setAttribute( 'value', serialize( $activeParticipants ) );
                $siteData->store();
            }
        }
        elseif( $removeIfNeeded )
        {
            $siteData->remove();
        }
    }

    public static function getStoredActivesParticipantsByPostId( $id )
    {
        $name = self::SITE_DATA_FIELD_PREFIX . $id;
        $siteData = eZSiteData::fetchByName( $name );
        $activeParticipants = array();
        if ( $siteData instanceof eZSiteData)
        {
            $activeParticipants = unserialize( $siteData->attribute( 'value' ) );
        }
        return $activeParticipants;
    }

    public function touch()
    {
        $this->setStatus();
    }

    public function setExpiry( $value )
    {
        $this->collaborationItem->setAttribute(
            self::COLLABORATION_FIELD_EXPIRY,
            self::expiryTimestamp( $this->collaborationItem->attribute( 'created' ), $value )
        );
        $this->collaborationItem->store();
    }

    public function setStatus( $status = null )
    {
        $timestamp = time();
        $object = $this->objectHelper->getContentObject();
        if ( $status !== null )
        {
            $this->collaborationItem->setAttribute( SensorPost::COLLABORATION_FIELD_STATUS, $status );
            $this->collaborationItem->setAttribute( 'modified', $timestamp );
            $this->collaborationItem->setAttribute( SensorPost::COLLABORATION_FIELD_LAST_CHANGE, $timestamp );

            if ( $status == SensorPost::STATUS_CLOSED )
            {
                $this->collaborationItem->setAttribute( 'status', eZCollaborationItem::STATUS_INACTIVE );
                $this->deactivateParticipants();
            }
            elseif ( $status == SensorPost::STATUS_WAITING )
            {
                $this->collaborationItem->setAttribute( 'status', eZCollaborationItem::STATUS_ACTIVE );
                $this->activateParticipants();
            }
            elseif ( $status == SensorPost::STATUS_REOPENED )
            {
                $this->collaborationItem->setAttribute( 'status', eZCollaborationItem::STATUS_ACTIVE );
                $this->activateParticipants();
            }
            $this->collaborationItem->sync();

        }
        if ( $object instanceof eZContentObject )
        {
            $this->objectHelper->setObjectState( $object, $status );

            $object->setAttribute( 'modified', $timestamp );
            $object->store();
            $this->storeActivesParticipants();
            eZSearch::addObject($object, true);
            eZContentCacheManager::clearContentCacheIfNeeded( $object->attribute( 'id' ) );

            if ( class_exists( 'Opencontent\Sensor\Legacy\CachePostService') )
                Opencontent\Sensor\Legacy\CachePostService::clearCache( $object->attribute( 'id' ) );
        }
    }

    public function addParticipant( $participantID, $participantRole )
    {
        $user = eZUser::fetch( $participantID );
        if ( $user instanceof eZUser )
        {
            $sensorUserInfo = SensorUserInfo::instance( $user );
            $sensorUserInfo->participateAs( $this, $participantRole );
            ezpEvent::getInstance()->notify( 'sensor/add_participant', array( $user, $participantRole, $this ) );
            $this->participantList = null;
        }
        else
        {
            throw new InvalidArgumentException( "User $participantID not found" );
        }
    }

    public function restoreParticipant( $participantID )
    {
        $user = eZUser::fetch( $participantID );
        if ( $user instanceof eZUser )
        {
            $sensorUserInfo = SensorUserInfo::instance( $user );
            $sensorUserInfo->restoreParticipation( $this );
            ezpEvent::getInstance()->notify( 'sensor/restore_participant', array( $user, $this ) );
            $this->participantList = null;
        }
        else
        {
            throw new InvalidArgumentException( "User $participantID not found" );
        }
    }

    public function trashParticipant( $participantID )
    {
        $user = eZUser::fetch( $participantID );
        if ( $user instanceof eZUser )
        {
            $sensorUserInfo = SensorUserInfo::instance( $user );
            $sensorUserInfo->trashParticipation( $this );
            ezpEvent::getInstance()->notify( 'sensor/trash_participant', array( $user, $this ) );
            $this->participantList = null;
        }
        else
        {
            throw new InvalidArgumentException( "User $participantID not found" );
        }
    }

    protected function getActivesParticipants()
    {
        $activeParticipants = array();
        $conditions = array(
            'collaboration_id' => $this->collaborationItem->attribute( 'id' ),
            'is_active' => 1
        );

        $resources = eZPersistentObject::fetchObjectList(
            eZCollaborationItemStatus::definition(),
            array( 'user_id' ),
            $conditions,
            null,
            null,
            false
        );

        foreach( $resources as $row )
        {
            $activeParticipants[] = $row['user_id'];
        }
        sort( $activeParticipants );
        return $activeParticipants;
    }

    protected function is( $key )
    {
        return $this->collaborationItem->attribute( self::COLLABORATION_FIELD_STATUS ) == $key;
    }

    public static function expiryTimestamp( $creationTimestamp, $days = null )
    {
        $creation = new DateTime();
        $creation->setTimestamp( $creationTimestamp );
        $creation->add( self::expiringInterval( $days ) );
        return $creation->format( 'U' );
    }

    protected static function expiringInterval( $days )
    {
        $expiringIntervalString = 'P' . intval( $days ) . 'D';
        $expiringInterval = new DateInterval( $expiringIntervalString );
        if ( !$expiringInterval instanceof DateInterval )
        {
            throw new Exception( "Invalid interval {$expiringIntervalString}" );
        }
        return $expiringInterval;
    }

    public static function getDateDiff( $start, $end = null )
    {
        if ( !( $start instanceof DateTime ) )
        {
            $start = new DateTime( $start );
        }

        if ( $end === null )
        {
            $end = new DateTime();
        }

        if ( !( $end instanceof DateTime ) )
        {
            $end = new DateTime( $start );
        }

        $interval = $end->diff( $start );
        $translate = function ( $nb, $str )
        {
            $string = $nb > 1 ? $str . 's' : $str;
            switch ( $string )
            {
                case 'year';
                    $string = ezpI18n::tr( 'sensor/expiring', 'anno' );
                    break;
                case 'years';
                    $string = ezpI18n::tr( 'sensor/expiring', 'anni' );
                    break;
                case 'month';
                    $string = ezpI18n::tr( 'sensor/expiring', 'mese' );
                    break;
                case 'months';
                    $string = ezpI18n::tr( 'sensor/expiring', 'mesi' );
                    break;
                case 'day';
                    $string = ezpI18n::tr( 'sensor/expiring', 'giorno' );
                    break;
                case 'days';
                    $string = ezpI18n::tr( 'sensor/expiring', 'giorni' );
                    break;
                case 'hour';
                    $string = ezpI18n::tr( 'sensor/expiring', 'ora' );
                    break;
                case 'hours';
                    $string = ezpI18n::tr( 'sensor/expiring', 'ore' );
                    break;
                case 'minute';
                    $string = ezpI18n::tr( 'sensor/expiring', 'minuto' );
                    break;
                case 'minutes';
                    $string = ezpI18n::tr( 'sensor/expiring', 'minuti' );
                    break;
                case 'second';
                    $string = ezpI18n::tr( 'sensor/expiring', 'secondo' );
                    break;
                case 'seconds';
                    $string = ezpI18n::tr( 'sensor/expiring', 'secondi' );
                    break;
            }
            return $string;
        };

        $format = array();
        if ( $interval->y !== 0 )
        {
            $format[] = "%y " . $translate( $interval->y, "year" );
        }
        if ( $interval->m !== 0 )
        {
            $format[] = "%m " . $translate( $interval->m, "month" );
        }
        if ( $interval->d !== 0 )
        {
            $format[] = "%d " . $translate( $interval->d, "day" );
        }
        if ( $interval->h !== 0 )
        {
            $format[] = "%h " . $translate( $interval->h, "hour" );
        }
        if ( $interval->i !== 0 )
        {
            $format[] = "%i " . $translate( $interval->i, "minute" );
        }
        if ( $interval->s !== 0 )
        {
            if ( !count( $format ) )
            {
                return ezpI18n::tr( 'sensor/expiring', 'meno di un minuto' );
            }
            else
            {
                $format[] = "%s " . $translate( $interval->s, "second" );
            }
        }

        // We use the two biggest parts
        if ( count( $format ) > 1 )
        {
            $format = array_shift( $format ) . " " . ezpI18n::tr( 'sensor/expiring', 'e' ) . " " . array_shift( $format );
        }
        else
        {
            $format = array_pop( $format );
        }

        return array( 'interval' => $interval, 'format' => $format );
    }

    public function delete()
    {
        $itemId = $this->collaborationItem->attribute( 'id' );
        self::deleteCollaborationStuff( $itemId );
    }

    public function commentsIsOpen()
    {
        if ( !$this->configParameters['CommentsAllowed'] )
        {
            return false;
        }
        $now = time();
        $resolutionTime = $this->getResolutionTime();
        if ( $resolutionTime['timestamp'] && $this->configParameters['CloseCommentsAfterSeconds'] )
        {
            return ( $now - $resolutionTime['timestamp'] ) < $this->configParameters['CloseCommentsAfterSeconds'];
        }
        return true;
    }

    public static function deleteCollaborationStuff( $itemId )
    {
        $db = eZDB::instance();
        $db->begin();
        $db->query( "DELETE FROM ezcollab_item WHERE id = $itemId" );
        $db->query( "DELETE FROM ezcollab_item_group_link WHERE collaboration_id = $itemId" );
        $res = $db->arrayQuery( "SELECT message_id FROM ezcollab_item_message_link WHERE collaboration_id = $itemId" );
        foreach( $res as $r )
        {
            $db->query( "DELETE FROM ezcollab_simple_message WHERE id = {$r['message_id']}" );
        }
        $db->query( "DELETE FROM ezcollab_item_message_link WHERE collaboration_id = $itemId" );
        $db->query( "DELETE FROM ezcollab_item_participant_link WHERE collaboration_id = $itemId" );
        $db->query( "DELETE FROM ezcollab_item_status WHERE collaboration_id = $itemId" );
        $db->commit();
    }

    public static function getCollaborationStuff( $itemId )
    {
        $db = eZDB::instance();
        $res['ezcollab_item'] = $db->arrayQuery( "SELECT * FROM ezcollab_item WHERE id = $itemId" );
        $res['ezcollab_item_group_link'] = $db->arrayQuery( "SELECT * FROM ezcollab_item_group_link WHERE collaboration_id = $itemId" );
        $tmp = $db->arrayQuery( "SELECT message_id FROM ezcollab_item_message_link WHERE collaboration_id = $itemId" );
        $res['ezcollab_simple_message'] = array();
        foreach( $tmp as $r )
        {
            $res['ezcollab_simple_message'][] = $db->arrayQuery( "SELECT * FROM ezcollab_simple_message WHERE id = {$r['message_id']}" );
        }
        $res['ezcollab_item_message_link'] = $db->arrayQuery( "SELECT * FROM ezcollab_item_message_link WHERE collaboration_id = $itemId" );
        $res['ezcollab_item_participant_link'] = $db->arrayQuery( "SELECT * FROM ezcollab_item_participant_link WHERE collaboration_id = $itemId" );
        $res['ezcollab_item_status'] = $db->arrayQuery( "SELECT * FROM ezcollab_item_status WHERE collaboration_id = $itemId" );
        return $res;
    }

    protected static function participantRoleName( $roleID )
    {    
        $roleNameMap = self::participantRoleNameMap();
        if ( isset( $roleNameMap[$roleID] ) )
        {
            return $roleNameMap[$roleID];
        }
        return null;
    }

    protected function participantRoleSortKey( $roleID )
    {
        $sorter = array(
            eZCollaborationItemParticipantLink::ROLE_STANDARD => 1000,
            eZCollaborationItemParticipantLink::ROLE_OBSERVER => 4,
            eZCollaborationItemParticipantLink::ROLE_OWNER => 3,
            eZCollaborationItemParticipantLink::ROLE_APPROVER => 2,
            eZCollaborationItemParticipantLink::ROLE_AUTHOR => 1
        );
        return isset( $sorter[$roleID] ) ? $sorter[$roleID] : 1000;
    }

    public static function participantRoleNameMap()
    {
        if ( empty( $GLOBALS['SensorParticipantRoleNameMap'] ) )
        {
            $GLOBALS['SensorParticipantRoleNameMap'] = array( 
               eZCollaborationItemParticipantLink::ROLE_AUTHOR => ezpI18n::tr( 'sensor/role_name', 'Autore' ),
               eZCollaborationItemParticipantLink::ROLE_APPROVER => ezpI18n::tr( 'sensor/role_name', 'Riferimento per il cittadino' ),               
               eZCollaborationItemParticipantLink::ROLE_OWNER => ezpI18n::tr( 'sensor/role_name', 'In carico a' ),                              
               eZCollaborationItemParticipantLink::ROLE_OBSERVER => ezpI18n::tr( 'sensor/role_name', 'Osservatore' ),
               eZCollaborationItemParticipantLink::ROLE_STANDARD => ezpI18n::tr( 'sensor/role_name', 'Standard' ),   
           );
        }
        return $GLOBALS['SensorParticipantRoleNameMap'];
    }

    public function addAttachment( eZHTTPFile $file )
    {
        $object = $this->objectHelper->getContentObject();
        $currentVersion = $object->attribute('current_version');
        $localeCode = eZContentObject::defaultLanguage();
        $mimeData = eZMimeType::findByFileContents($file->attribute("original_filename"));
        $dataMap = $object->dataMap();
        if (isset($dataMap['attachment'])) {
            $result = array();
            $status = $dataMap['attachment']->insertHTTPFile($object, $currentVersion, $localeCode, $file, $mimeData, $result);

            if (!$status) {
                eZDebug::writeError(
                    ezpI18n::tr('kernel/content/upload',
                        'The attribute %class_identifier does not support HTTP file storage.', null,
                        array('%class_identifier' => $object->attribute('class_identifier'))), __METHOD__);
                return false;
            }

            if ($result['require_storage']) {
                $dataMap['attachment']->store();
            }

            return $dataMap['attachment']->content();
        }

        return false;
    }

    public function removeAttachment($filename)
    {
        $object = $this->objectHelper->getContentObject();
        $dataMap = $object->dataMap();
        if (isset($dataMap['attachment'])) {
            if ($dataMap['attachment']->attribute('data_type_string') == OCMultiBinaryType::DATA_TYPE_STRING){
                foreach ((array)$filename as $item) {
                    $http = eZHTTPTool::instance();
                    $postValue = [];
                    $postValue[$dataMap['attachment']->attribute('id') . '_delete_multibinary'][$item] = 1;
                    $http->setPostVariable('CustomActionButton', $postValue);
                    $dataMap['attachment']->customHTTPAction($http, 'delete_multibinary', []);
                }
            }elseif ($dataMap['attachment']->attribute('data_type_string') == eZBinaryFileType::DATA_TYPE_STRING){
                $http = eZHTTPTool::instance();
                $dataMap['attachment']->customHTTPAction($http, 'delete_binary', []);
            }
        }

        return false;
    }
}
