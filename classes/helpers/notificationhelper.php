<?php

class SensorNotificationHelper
{
    /**
     * @var SensorPost
     */
    protected $post;

    protected function __construct( SensorPost $post = null )
    {
        $this->post = $post;
    }

    public static function instance( SensorPost $post = null )
    {
        return new SensorNotificationHelper( $post );
    }

    /**
     * @param eZNotificationEvent $event
     * @param array $parameters
     *
     * @return int
     * @throws Exception
     */
    public function handleEvent( eZNotificationEvent $event, array &$parameters )
    {
        if ( $this->post instanceof SensorPost )
        {
            $eventType = $event->attribute( 'data_text1' );
            $prefix = SensorHelper::factory()->getSensorCollaborationHandlerTypeString() . '_';
            $eventIdentifier = str_replace( $prefix, '', $eventType );
            $searchRules = array( $prefix . $eventIdentifier );

            $participantIdList = $this->post->getParticipants( null, true );
            $ruleList = array();
            foreach ( $participantIdList as $roleGroup )
            {
                foreach ( $roleGroup['items'] as $item )
                {
                    $user = eZUser::fetch( $item['id'] );
                    if ( !$user instanceof eZUser )
                    {
                        continue;
                    }
                    $userInfo = SensorUserInfo::instance( $user );

                    foreach (
                        self::languageNotificationTypes(
                            $userInfo
                        ) as $languageNotification
                    )
                    {
                        if ( $languageNotification['parent'] == $eventIdentifier )
                        {
                            $searchRules[] = $prefix . $languageNotification['identifier'];
                        }
                    }

                    foreach (
                        self::transportNotificationTypes(
                            $userInfo
                        ) as $transportNotification
                    )
                    {
                        if ( $transportNotification['parent'] == $eventIdentifier )
                        {
                            $searchRules[] = $prefix . $transportNotification['identifier'];
                        }
                    }

                    $rules = eZCollaborationNotificationRule::fetchItemTypeList(
                        $searchRules,
                        array( $item['id'] ),
                        false
                    );

                    $ruleListItem = array(
                        'id' => $item['id'],
                        'email' => $user->attribute( 'email' ),
                        'whatsapp' => $userInfo->whatsAppId(),
                        'event_type' => $eventType,
                    );

                    $hasCurrentRule = false;

                    $ruleListItem['transport'] = array();
                    foreach ( $rules as $rule )
                    {
                        if ( $rule['collab_identifier'] == $eventType )
                        {
                            $hasCurrentRule = true;
                        }
                        foreach (
                            self::languageNotificationTypes(
                                $userInfo
                            ) as $languageNotification
                        )
                        {
                            if ( $rule['collab_identifier'] == $prefix . $languageNotification['identifier'] )
                            {
                                $ruleListItem['language'] = str_replace(
                                    $eventType . ':',
                                    '',
                                    $rule['collab_identifier']
                                );
                            }
                        }
                        foreach (
                            self::transportNotificationTypes(
                                $userInfo
                            ) as $transportNotification
                        )
                        {
                            if ( $rule['collab_identifier'] == $prefix . $transportNotification['identifier'] )
                            {
                                $ruleListItem['transport'][] = str_replace(
                                    $eventType . ':',
                                    '',
                                    $rule['collab_identifier']
                                );
                            }
                        }
                    }

                    if ( !isset( $ruleListItem['language'] ) )
                    {
                        $ruleListItem['language'] = $userInfo->attribute(
                            'default_notification_language'
                        );
                    }
                    //                if ( !isset( $ruleListItem['transport'] ) )
                    //                {
                    //                    $ruleListItem['transport'] = $userInfo->attribute(
                    //                        'default_notification_transport'
                    //                    );
                    //                }
                    if ( $hasCurrentRule && count( $ruleListItem['transport'] ) > 0 )
                    {
                        foreach ( $ruleListItem['transport'] as $transport )
                        {
                            $ruleList[$transport][$roleGroup['role_id']][] = $ruleListItem;
                        }
                    }
                }
            }

            foreach ( $ruleList as $transport => $userList )
            {
                if ( $transport == 'ezmail' )
                {
                    $this->createMailNotificationCollections( $eventIdentifier, $event, $userList, $parameters );
                }

                if ( $transport == 'ezwhatsapp' )
                {
                    $this->createWhatsAppNotificationCollections( $eventIdentifier, $event, $userList, $parameters );
                }

                if ( $transport == 'ezmaildigest' )
                {
                    $this->createMailDigestNotificationCollections( $eventIdentifier, $event, $userList, $parameters );
                }

            }
            return eZNotificationEventHandler::EVENT_HANDLED;
        }
        else
        {
            eZDebug::writeError( "Post not found", __METHOD__ );
            return eZNotificationEventHandler::EVENT_SKIPPED;
        }
    }

    public function sendNotifications( eZNotificationEvent $event, array $parameters )
    {
        /** @var eZNotificationCollection[] $collections */
        $collections = eZNotificationCollection::fetchListForHandler(
            eZCollaborationNotificationHandler::NOTIFICATION_HANDLER_ID,
            $event->attribute( 'id' ),
            eZCollaborationNotificationHandler::TRANSPORT
        );


        echo '<pre>';
        print_r($collections);

        foreach ( $collections as $collection )
        {
            /** @var eZNotificationCollectionItem[] $items */
            $items = $collection->attribute( 'items_to_send' );
            print_r($items);
            exit;


            $addressList = array();
            foreach ( $items as $item )
            {
                $addressList[] = $item->attribute( 'address' );
                $item->remove();
            }
            /** @var eZMailNotificationTransport $transport */
            $transport = eZNotificationTransport::instance( 'ezmail' );
            $transport->send( $addressList,
                $collection->attribute( 'data_subject' ),
                $collection->attribute( 'data_text' ),
                null,
                $parameters );
            if ( $collection->attribute( 'item_count' ) == 0 )
            {
                $collection->remove();
            }
        }
    }

    protected function createMailNotificationCollections( $eventIdentifier, eZNotificationEvent $event, $userCollection, &$parameters )
    {
        $db = eZDB::instance();
        $db->begin();

        $eventCreator = $event->attribute( SensorPostEventHelper::EVENT_CREATOR_FIELD );
        $eventTimestamp = $event->attribute( SensorPostEventHelper::EVENT_TIMESTAMP_FIELD );
        $eventDetails = json_decode( $event->attribute( SensorPostEventHelper::EVENT_DETAILS_FIELD ), true );

        $tpl = eZTemplate::factory();
        $tpl->resetVariables();

        $tpl->setVariable( 'event_identifier', $eventIdentifier );
        $tpl->setVariable( 'event_details', $eventDetails );
        $tpl->setVariable( 'event_creator', $eventCreator );
        $tpl->setVariable( 'event_timestamp', $eventTimestamp );

        foreach( $userCollection as $participantRole => $collectionItems )
        {
            $tpl->setVariable( 'subject', '' );
            $tpl->setVariable( 'body', '' );
            $templateName = self::notificationMailTemplate( $participantRole );

            if ( !$templateName ) continue;

            $templatePath = 'design:sensor/mail/' . $eventIdentifier . '/' . $templateName;

            $tpl->setVariable( 'collaboration_item', $this->post->getCollaborationItem() );
            $tpl->setVariable( 'collaboration_participant_role', $participantRole );
            $tpl->setVariable( 'collaboration_item_status', $this->post->getCollaborationItem()->attribute( SensorPost::COLLABORATION_FIELD_STATUS ) );
            $tpl->setVariable( 'sensor_post', $this->post );
            $tpl->setVariable( 'object', $this->post->objectHelper->getContentObject() );
            $tpl->setVariable( 'node', $this->post->objectHelper->getContentObject()->attribute( 'main_node' ) );

            $tpl->fetch( $templatePath );

            $body = trim( $tpl->variable( 'body' ) );
            $subject = $tpl->variable( 'subject' );

            if ( $body != '' )
            {
                $tpl->setVariable( 'title', $subject );
                $tpl->setVariable( 'content', $body );
                $templateResult = $tpl->fetch( 'design:mail/sensor_mail_pagelayout.tpl' );

                if ( $tpl->hasVariable( 'message_id' ) )
                {
                    $parameters['message_id'] = $tpl->variable( 'message_id' );
                }
                if ( $tpl->hasVariable( 'references' ) )
                {
                    $parameters['references'] = $tpl->variable( 'references' );
                }
                if ( $tpl->hasVariable( 'reply_to' ) )
                {
                    $parameters['reply_to'] = $tpl->variable( 'reply_to' );
                }
                if ( $tpl->hasVariable( 'from' ) )
                {
                    $parameters['from'] = $tpl->variable( 'from' );
                }
                if ( $tpl->hasVariable( 'content_type' ) )
                {
                    $parameters['content_type'] = $tpl->variable( 'content_type' );
                }
                else
                {
                    $parameters['content_type'] = 'text/html';
                }

                $collection = eZNotificationCollection::create(
                    $event->attribute( 'id' ),
                    eZCollaborationNotificationHandler::NOTIFICATION_HANDLER_ID,
                    'ezmail'
                );

                $collection->setAttribute( 'data_subject', $subject );
                $collection->setAttribute( 'data_text', $templateResult );
                $collection->store();

                $locale = eZLocale::instance();
                $weekDayNames = $locale->attribute( 'weekday_name_list' );
                $weekDaysByName = array_flip( $weekDayNames );

                foreach ( $collectionItems as $collectionItem )
                {
                    $item = $collection->addItem( $collectionItem['email'] );
                    $settings = eZGeneralDigestUserSettings::fetchByUserId( $collectionItem['id'] );
                    if ( $settings !== null && $settings->attribute( 'receive_digest' ) == 1 )
                    {
                        $time = $settings->attribute( 'time' );
                        $timeArray = explode( ':', $time );
                        $hour = $timeArray[0];

                        if ( $settings->attribute( 'digest_type' ) == eZGeneralDigestUserSettings::TYPE_DAILY )
                        {
                            eZNotificationSchedule::setDateForItem( $item, array( 'frequency' => 'day',
                                'hour' => $hour ) );
                        }
                        else if ( $settings->attribute( 'digest_type' ) == eZGeneralDigestUserSettings::TYPE_WEEKLY )
                        {
                            $weekday = $weekDaysByName[ $settings->attribute( 'day' ) ];
                            eZNotificationSchedule::setDateForItem( $item, array( 'frequency' => 'week',
                                'day' => $weekday,
                                'hour' => $hour ) );
                        }
                        else if ( $settings->attribute( 'digest_type' ) == eZGeneralDigestUserSettings::TYPE_MONTHLY )
                        {
                            eZNotificationSchedule::setDateForItem( $item,
                                array( 'frequency' => 'month',
                                    'day' => $settings->attribute( 'day' ),
                                    'hour' => $hour ) );
                        }
                        $item->store();
                    }
                }
            }
        }

        $db->commit();
    }

    protected function createWhatsAppNotificationCollections( $eventIdentifier, eZNotificationEvent $event, $userCollection, &$parameters )
    {
        if ( class_exists( 'OCWhatsAppConnector' ) )
        {

            $db = eZDB::instance();
            $db->begin();

            $tpl = eZTemplate::factory();
            $tpl->resetVariables();

            foreach ( $userCollection as $participantRole => $collectionItems )
            {
                $templateName = $this->notificationMailTemplate( $participantRole );
                $templatePath = 'design:sensor/whatsapp/' . $templateName;

                $tpl->setVariable( 'collaboration_item', $this->post->getCollaborationItem() );
                $tpl->setVariable( 'collaboration_participant_role', $participantRole );
                $tpl->setVariable(
                    'collaboration_item_status',
                    $this->post->getCollaborationItem()->attribute(
                        SensorPost::COLLABORATION_FIELD_STATUS
                    )
                );
                $tpl->setVariable( 'post_url', $this->post->objectHelper->getPostUrl() );
                $tpl->setVariable( 'object', $this->post->objectHelper->getContentObject() );
                $tpl->setVariable(
                    'node',
                    $this->post->objectHelper->getContentObject()->attribute( 'main_node' )
                );

                $message = trim( $tpl->fetch( $templatePath ) );

                if ( $message != '' )
                {

                    eZDebug::writeNotice( $message, __METHOD__ );

                    //                $collection = eZNotificationCollection::create(
                    //                    $event->attribute( 'id' ),
                    //                    eZCollaborationNotificationHandler::NOTIFICATION_HANDLER_ID,
                    //                    'ezwhatsapp'
                    //                );
                    //                $collection->setAttribute( 'data_text', $templateResult );
                    //                foreach ( $collectionItems as $collectionItem )
                    //                {
                    //                    $collection->addItem( $collectionItem['whatsapp'] );
                    //                }
                    $waUserId = SensorHelper::factory()->getWhatsAppUserId();
                    try
                    {
                        $wa = OCWhatsAppConnector::instanceFromContentObjectId( $waUserId );
                        if ( $wa instanceof OCWhatsAppConnector )
                        {
                            foreach ( $collectionItems as $collectionItem )
                            {
                                $wa->sendMessage( $collectionItem['whatsapp'], $message );
                            }
                        }
                    }
                    catch ( Exception $e )
                    {
                        eZDebug::writeError(
                            $e->getMessage() . ' ' . $e->getTraceAsString(),
                            __METHOD__
                        );
                    }
                }
            }

            $db->commit();
        }
    }

    protected function createMailDigestNotificationCollections( $eventIdentifier, eZNotificationEvent $event, $userCollection, &$parameters )
    {
        $db = eZDB::instance();
        $db->begin();

        $eventCreator = $event->attribute( SensorPostEventHelper::EVENT_CREATOR_FIELD );
        $eventTimestamp = $event->attribute( SensorPostEventHelper::EVENT_TIMESTAMP_FIELD );
        $eventDetails = json_decode( $event->attribute( SensorPostEventHelper::EVENT_DETAILS_FIELD ), true );

        $tpl = eZTemplate::factory();
        $tpl->resetVariables();

        $tpl->setVariable( 'event_identifier', $eventIdentifier );
        $tpl->setVariable( 'event_details', $eventDetails );
        $tpl->setVariable( 'event_creator', $eventCreator );
        $tpl->setVariable( 'event_timestamp', $eventTimestamp );


        echo '<pre>';
        print_r($event);
        exit;


        foreach( $userCollection as $participantRole => $collectionItems )
        {
            $tpl->setVariable( 'subject', '' );
            $tpl->setVariable( 'body', '' );
            $templateName = self::notificationMailTemplate( $participantRole );

            if ( !$templateName ) continue;

            $templatePath = 'design:sensor/mail/' . $eventIdentifier . '/' . $templateName;

            $tpl->setVariable( 'collaboration_item', $this->post->getCollaborationItem() );
            $tpl->setVariable( 'collaboration_participant_role', $participantRole );
            $tpl->setVariable( 'collaboration_item_status', $this->post->getCollaborationItem()->attribute( SensorPost::COLLABORATION_FIELD_STATUS ) );
            $tpl->setVariable( 'sensor_post', $this->post );
            $tpl->setVariable( 'object', $this->post->objectHelper->getContentObject() );
            $tpl->setVariable( 'node', $this->post->objectHelper->getContentObject()->attribute( 'main_node' ) );

            $tpl->fetch( $templatePath );

            $body = trim( $tpl->variable( 'body' ) );
            $subject = $tpl->variable( 'subject' );

            if ( $body != '' )
            {
                $tpl->setVariable( 'title', $subject );
                $tpl->setVariable( 'content', $body );
                $templateResult = $tpl->fetch( 'design:mail/sensor_mail_pagelayout.tpl' );

                if ( $tpl->hasVariable( 'message_id' ) )
                {
                    $parameters['message_id'] = $tpl->variable( 'message_id' );
                }
                if ( $tpl->hasVariable( 'references' ) )
                {
                    $parameters['references'] = $tpl->variable( 'references' );
                }
                if ( $tpl->hasVariable( 'reply_to' ) )
                {
                    $parameters['reply_to'] = $tpl->variable( 'reply_to' );
                }
                if ( $tpl->hasVariable( 'from' ) )
                {
                    $parameters['from'] = $tpl->variable( 'from' );
                }
                if ( $tpl->hasVariable( 'content_type' ) )
                {
                    $parameters['content_type'] = $tpl->variable( 'content_type' );
                }
                else
                {
                    $parameters['content_type'] = 'text/html';
                }

                $collection = eZNotificationCollection::create(
                    $event->attribute( 'id' ),
                    eZCollaborationNotificationHandler::NOTIFICATION_HANDLER_ID,
                    'ezmail'
                );

                $collection->setAttribute( 'data_subject', $subject );
                $collection->setAttribute( 'data_text', $templateResult );
                $collection->store();
                foreach ( $collectionItems as $collectionItem )
                {
                    $collection->addItem( $collectionItem['email'] );
                }
            }
        }

        $db->commit();
    }

    public static function notificationMailTemplate( $participantRole )
    {
        if ( $participantRole == eZCollaborationItemParticipantLink::ROLE_APPROVER )
        {
            return 'approver.tpl';
        }
        else if ( $participantRole == eZCollaborationItemParticipantLink::ROLE_AUTHOR )
        {
            return 'author.tpl';
        }
        else if ( $participantRole == eZCollaborationItemParticipantLink::ROLE_OBSERVER )
        {
            return 'observer.tpl';
        }
        else if ( $participantRole == eZCollaborationItemParticipantLink::ROLE_OWNER )
        {
            return 'owner.tpl';
        }
        else
            return false;
    }

    public function notificationTypes()
    {
        return array_merge(
            $this->postNotificationTypes(),
            $this->transportNotificationTypes(),
            $this->languageNotificationTypes()
        );
    }

    public function postNotificationTypes()
    {
        $postNotificationTypes = array();

        $postNotificationTypes[] = array(
            'identifier' => 'on_create',
            'name' => ezpI18n::tr(
                'sensor/notification',
                'Creazione di una segnalazione'
            ),
            'description' => ezpI18n::tr(
                'sensor/notification',
                'Ricevi una notifica alla creazione di una segnalazione'
            ),
            'group' => 'standard'
        );

        $postNotificationTypes[] = array(
            'identifier' => 'on_assign',
            'name' => ezpI18n::tr(
                'sensor/notification',
                'Assegnazione di una segnalazione'
            ),
            'description' => ezpI18n::tr(
                'sensor/notification',
                'Ricevi una notifica quando una tua segnalazione è assegnata a un responsabile'
            ),
            'group' => 'standard'
        );

        $postNotificationTypes[] = array(
            'identifier' => 'on_add_observer',
            'name' => ezpI18n::tr(
                'sensor/notification',
                'Coinvolgimento di un osservatore'
            ),
            'description' => ezpI18n::tr(
                'sensor/notification',
                'Ricevi una notifica quando un osservatore viene coinvolto in una segnalazione'
            ),
            'group' => 'standard'
        );

        $postNotificationTypes[] = array(
            'identifier' => 'on_add_comment',
            'name' => ezpI18n::tr(
                'sensor/notification',
                'Commento a una segnalazione'
            ),
            'description' => ezpI18n::tr(
                'sensor/notification',
                'Ricevi una notifica quando viene aggiunto un commento ad una tua segnalazione'
            ),
            'group' => 'standard'
        );

        $postNotificationTypes[] = array(
            'identifier' => 'on_fix',
            'name' => ezpI18n::tr(
                'sensor/notification',
                'Intervento terminato'
            ),
            'description' => ezpI18n::tr(
                'sensor/notification',
                "Ricevi una notifica quando un responsabile ha completato l'attività che riguarda una tua segnalazione"
            ),
            'group' => 'standard'
        );

        $postNotificationTypes[] = array(
            'identifier' => 'on_close',
            'name' => ezpI18n::tr(
                'sensor/notification',
                'Chiusura di una segnalazione'
            ),
            'description' => ezpI18n::tr(
                'sensor/notification',
                "Ricevi una notifica quando una tua segnalazione è stata chiusa"
            ),
            'group' => 'standard'
        );

        $config = SensorHelper::factory()->getSensorConfigParams();
        if ( $config['AuthorCanReopen'] )
        {
            $postNotificationTypes[] = array(
                'identifier' => 'on_reopen',
                'name' => ezpI18n::tr(
                    'sensor/notification',
                    'Riapertura di una segnalazione'
                ),
                'description' => ezpI18n::tr(
                    'sensor/notification',
                    "Ricevi una notifica alla riapertura di una tua segnalazione"
                ),
                'group' => 'standard'
            );
        }

        return $postNotificationTypes;
    }

    protected function languageNotificationTypes( SensorUserInfo $userInfo = null )
    {
        if ( $userInfo === null )
        {
            $userInfo = SensorUserInfo::current();
        }
        $languagesNotificationTypes = array();
        /** @var eZContentLanguage[] $languages */
        //$languages = eZContentLanguage::prioritizedLanguages();
        //$defaultLanguageCode = $userInfo->attribute( 'default_notification_language' );
        //if ( count( $languages ) > 1 )
        //{
        //    foreach( self::postNotificationTypes() as $type )
        //    {
        //        foreach ( $languages as $language )
        //        {
        //            $languagesNotificationTypes[] = array(
        //                'name' => $language->attribute( 'name' ),
        //                'identifier' => $type['identifier'] . ':' . $language->attribute( 'locale' ),
        //                'description' => ezpI18n::tr(
        //                    'sensor/notification',
        //                    'In che lingua vuoi ricevere le notifiche?'
        //                ),
        //                'language_code' => $language->attribute( 'locale' ),
        //                'default_language_code' => $defaultLanguageCode,
        //                'parent' => $type['identifier'],
        //                'group' => 'language'
        //            );
        //        }
        //    }
        //}
        return $languagesNotificationTypes;
    }

    protected function transportNotificationTypes( SensorUserInfo $userInfo = null )
    {
        if ( $userInfo === null )
        {
            $userInfo = SensorUserInfo::current();
        }
        $transportNotificationTypes = array();
        $defaultTransport = $userInfo->attribute( 'default_notification_transport' );
        foreach( self::postNotificationTypes() as $type )
        {
            $transportNotificationTypes[] = array(
                'name' => 'Email istantanea',
                'identifier' => $type['identifier'] . ':ezmail',
                'description' => ezpI18n::tr(
                    'sensor/notification',
                    'Ricevi la notifica via mail'
                ),
                'transport' => 'ezmail',
                'default_transport' => $defaultTransport,
                'parent' => $type['identifier'],
                'group' => 'transport',
                'enabled' => $defaultTransport == 'ezmail'
            );

            /*$transportNotificationTypes[] = array(
                'name' => 'Riepilogo giornaliero',
                'identifier' => $type['identifier'] . ':ezmaildigest',
                'description' => ezpI18n::tr(
                    'sensor/notification',
                    'Ricevi la notifica via mail'
                ),
                'transport' => 'ezmaildigest',
                'default_transport' => $defaultTransport,
                'parent' => $type['identifier'],
                'group' => 'transport',
                'enabled' => true
            );*/

//            if ( class_exists( 'OCWhatsAppConnector' ) && $userInfo->whatsAppId() )
//            {
//                $transportNotificationTypes[] = array(
//                    'name' => 'WhatsApp',
//                    'identifier' => $type['identifier'] . ':ezwhatsapp',
//                    'description' => ezpI18n::tr(
//                        'sensor/notification',
//                        'Ricevi la notifica via WhatsApp'
//                    ),
//                    'transport' => 'ezwhatsapp',
//                    'default_transport' => $defaultTransport,
//                    'parent' => $type['identifier'],
//                    'group' => 'transport',
//                    'enabled' => $type['identifier'] != 'on_create' && $userInfo->whatsAppId()
//                    //@todo
//                );
//            }
        }
        return $transportNotificationTypes;
    }

    public function removeNotificationRules( $userId, $typeIdentifiersFilters = null, $transport = null, $language = null )
    {
        $user = eZUser::fetch( $userId );
        if (!$user instanceof eZUser){
            throw new Exception("User ($userId) not found");
        }
        $userInfo = SensorUserInfo::instance( $user );
        $prefix = SensorHelper::factory()->getSensorCollaborationHandlerTypeString() . '_';

        if (!$transport) {
            $transport = $userInfo->attribute('default_notification_transport');
        }
        if (!$language) {
            $language = $userInfo->attribute('default_notification_language');
        }
        $postNotificationTypes = SensorNotificationHelper::instance()->postNotificationTypes();

        foreach ( $postNotificationTypes as $notificationType )
        {
            if ( is_array( $typeIdentifiersFilters ) && !in_array( $notificationType['identifier'], $typeIdentifiersFilters ) )
            {
                continue;
            }

            $notificationRules[] = $prefix . $notificationType['identifier'];
            $notificationRules[] = $prefix . $notificationType['identifier'] . ':' . $transport;
            $notificationRules[] = $prefix . $notificationType['identifier'] . ':' . $language;
        }

        if ( !empty( $notificationRules ) )
        {
            $db = eZDB::instance();
            $db->begin();

            /** @var eZCollaborationNotificationRule[] $subscriptions */
            $subscriptions = (array)eZPersistentObject::fetchObjectList(
                eZCollaborationNotificationRule::definition(),
                null,
                array('user_id' => $userId, 'collab_identifier' => array($notificationRules))
            );
            foreach($subscriptions as $subscription){
                $subscription->remove();
            }

            $db->commit();
        }
    }

    public function storeNotificationRules( $userId, $typeIdentifiersFilters = null, $transport = null, $language = null )
    {
        try
        {
            $user = eZUser::fetch( $userId );
            if (!$user instanceof eZUser){
                throw new Exception("User ($userId) not found");
            }
            $userInfo = SensorUserInfo::instance( $user );
            $prefix = SensorHelper::factory()->getSensorCollaborationHandlerTypeString() . '_';

            if (!$transport) {
                $transport = $userInfo->attribute('default_notification_transport');
            }
            if (!$language) {
                $language = $userInfo->attribute('default_notification_language');
            }
            $postNotificationTypes = SensorNotificationHelper::instance()->postNotificationTypes();

            $notificationRules = array();
            foreach ( $postNotificationTypes as $notificationType )
            {
                if ( is_array( $typeIdentifiersFilters ) && !in_array( $notificationType['identifier'], $typeIdentifiersFilters ) )
                {
                    continue;
                }

                $notificationRules[] = $prefix . $notificationType['identifier'];
                $notificationRules[] = $prefix . $notificationType['identifier'] . ':' . $transport;
                $notificationRules[] = $prefix . $notificationType['identifier'] . ':' . $language;
            }

            if ( !empty( $notificationRules ) )
            {
                $db = eZDB::instance();
                $db->begin();
                foreach( $notificationRules as $rule )
                {
                    eZCollaborationNotificationRule::create( $rule, $userId )->store();
                }

                $db->commit();
            }
        }
        catch( Exception $e )
        {
            eZDebug::writeError( $e->getMessage(), __METHOD__ );
        }
    }

    public function storeDefaultNotificationRules( $userId )
    {
        $this->storeNotificationRules( $userId, array( 'on_create', 'on_assign', 'on_close' ) );
    }

    public static function onSocialUserSignup( $userId )
    {
        SensorNotificationHelper::instance()->storeDefaultNotificationRules( $userId );
    }

    public function getNotificationSubscriptionsForUser($userId, $subIdentifier = null)
    {
        $notificationPrefix = SensorHelper::factory()->getSensorCollaborationHandlerTypeString() . '_';
        $notificationTypes = $this->postNotificationTypes();
        $searchNotificationRules = array();
        foreach ($notificationTypes as $type) {
            if ($subIdentifier) {
                $searchNotificationRules[] = $notificationPrefix . $type['identifier'] . ':' . $subIdentifier;
            } else {
                $searchNotificationRules[] = $notificationPrefix . $type['identifier'];
            }
        }
        /** @var eZCollaborationNotificationRule[] $subscriptions */
        $subscriptions = (array)eZPersistentObject::fetchObjectList(
            eZCollaborationNotificationRule::definition(),
            null,
            array('user_id' => $userId, 'collab_identifier' => array($searchNotificationRules))
        );

        $result = array();
        $notificationPrefix = SensorHelper::factory()->getSensorCollaborationHandlerTypeString() . '_';
        $notificationTypes = $this->postNotificationTypes();
        foreach ($subscriptions as $subscription) {
            $collaborationIdentifier = $subscription->attribute('collab_identifier');
            $identifier = str_replace($notificationPrefix, '', $collaborationIdentifier);
            if ($subIdentifier) {
                $identifier = str_replace(':' . $subIdentifier, '', $identifier);
            }
            $result[] = array(
                'collab_identifier' => $collaborationIdentifier,
                'identifier' => $identifier,
                'subType' => $subIdentifier,
                'name' => array_reduce( $notificationTypes, function($carry, $item) use($identifier){
                    if ($item['identifier'] == $identifier){
                        $carry = $item['name'];
                    }
                    return $carry;
                })
            );

        }

        return $result;
    }

}
