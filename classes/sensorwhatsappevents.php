<?php

class SensorWhatsAppEvents extends AllEvents
{
    const UPDATE_LIMIT_SECONDS = 120;

    /**
     * @var eZCLI
     */
    protected $cli;

    public $activeEvents = array(
        //        'onClose',
        //        'onCodeRegister',
        //        'onCodeRegisterFailed',
        //        'onCodeRequest',
        //        'onCodeRequestFailed',
        //        'onCodeRequestFailedTooRecent',
        'onConnect',
        'onConnectError',
        'onCredentialsBad',
        //        'onCredentialsGood',
        'onDisconnect',
        //        'onDissectPhone',
        //        'onDissectPhoneFailed',
        'onGetAudio',
        //        'onGetBroadcastLists',
        //        'onGetError',
        //        'onGetExtendAccount',
        //        'onGetGroupMessage',
        //        'onGetGroupParticipants',
        //        'onGetGroups',
        //        'onGetGroupsInfo',
        //        'onGetGroupsSubject',
        'onGetImage',
        'onGetLocation',
        'onGetMessage',
        //        'onGetNormalizedJid',
        //        'onGetPrivacyBlockedList',
        //        'onGetProfilePicture',
        //        'onGetReceipt',
        //        'onGetRequestLastSeen',
        //        'onGetServerProperties',
        //        'onGetServicePricing',
        //        'onGetStatus',
        //        'onGetSyncResult',
        'onGetVideo',
        //        'onGetvCard',
        //        'onGroupCreate',
        //        'onGroupisCreated',
        //        'onGroupsChatCreate',
        //        'onGroupsChatEnd',
        //        'onGroupsParticipantsAdd',
        //        'onGroupsParticipantsPromote',
        //        'onGroupsParticipantsRemove',
        //        'onLogin',
        'onLoginFailed',
        //        'onAccountExpired',
        //        'onMediaMessageSent',
        //        'onMediaUploadFailed',
        //        'onMessageComposing',
        //        'onMessagePaused',
        //        'onMessageReceivedClient',
        //        'onMessageReceivedServer',
        //        'onPaidAccount',
        'onPing',
        'onPresenceAvailable',
        //        'onPresenceUnavailable',
        //        'onProfilePictureChanged',
        //        'onProfilePictureDeleted',
        //        'onSendMessage',
        'onSendMessageReceived',
        //        'onSendPong',
        //        'onSendPresence',
        //        'onSendStatusUpdate',
        //        'onStreamError',
        //        'onUploadFile',
        //        'onUploadFileFailed',
    );

    public function __construct( WhatsProt $whatsProt )
    {
        $this->whatsProt = $whatsProt;
        $this->cli = eZCLI::instance();
        return $this;
    }

    public function onConnect( $mynumber, $socket )
    {
        $this->cli->output( "Phone number $mynumber connected successfully!" );
    }

    public function onConnectError( $mynumber, $socket )
    {
        $this->cli->error( "Error on connecting!" );
    }

    public function onCredentialsBad($mynumber, $status, $reason)
    {
        $this->cli->error( "Error: $status, $reason" );
    }

    public function onDisconnect($mynumber, $socket)
    {
        $this->cli->error( "Phone number $mynumber is disconnected!" );
    }

    public function onPresenceAvailable($mynumber, $from)
    {
        $this->cli->output( $from );
    }

    public function onLoginFailed($mynumber, $data)
    {
        $this->cli->error( "Error: $data" );
    }

    public function onPing($mynumber, $id)
    {
    }

    public function onSendMessageReceived($mynumber, $id, $from, $type)
    {
    }

    public function onGetMessage( $mynumber, $from, $id, $type, $time, $name, $body )
    {
        $user = $this->getUser( $from, $name );
        if ( strpos( $body, '#help' ) !== false )
        {
            $this->sendHelp( $user );
        }
        elseif ( strpos( $body, '#password' ) !== false )
        {
            $this->setPassword( $user, $body );
        }
        else
        {
            $data = array(
                'type' => 'segnalazione',
                'subject' => substr( $body, 0, 20 ) . '...',
                'description' => $body
            );
            $this->createPost( $user, $data, $time );
        }
    }

    public function onGetImage($mynumber, $from, $id, $type, $time, $name, $size, $url, $file, $mimeType, $fileHash, $width, $height, $preview, $caption)
    {
        $user = $this->getUser( $from, $name );
        $imageFile = self::getRemoteFile( $url );
        $data = array(
            'image' => $imageFile . '|' . $caption
        );
        if ( !empty( $caption ) )
        {
            $data['subject'] = $caption;
        }
        $this->createPost( $user, $data, $time );
    }

    public function onGetAudio($mynumber, $from, $id, $type, $time, $name, $size, $url, $file, $mimeType, $fileHash, $duration, $acodec, $fromJID_ifGroup = null)
    {
        $user = $this->getUser( $from, $name );
        $this->cli->warning( "Audio ($time) from $name:\n$file\n" );
        if ( $user instanceof SensorUserInfo )
        {
            $this->whatsProt->sendMessage( $user->whatsAppId(), 'Al momento i file audio non sono supportati da SensorCivico' );
        }
    }

    public function onGetVideo($mynumber, $from, $id, $type, $time, $name, $url, $file, $size, $mimeType, $fileHash, $duration, $vcodec, $acodec, $preview, $caption)
    {
        $user = $this->getUser( $from, $name );
        $this->cli->warning( "Video ($time) from $name:\n$file\n" );
        if ( $user instanceof SensorUserInfo )
        {
            $this->whatsProt->sendMessage( $user->whatsAppId(), 'Al momento i file video non sono supportati da SensorCivico' );
        }
    }

    public function onGetLocation($mynumber, $from, $id, $type, $time, $name, $author, $longitude, $latitude, $url, $preview, $fromJID_ifGroup = null)
    {
        $user = $this->getUser( $from, $name );
        $data = array(
            'geo' => "1|#$latitude|#$longitude|#"
        );
        $this->createPost( $user, $data, $time );
    }

    protected function sendHelp( SensorUserInfo $user )
    {
        $url = SensorHelper::factory()->siteUrl();
        $message = "Per accedere al sistema da web cai su {$url} e usa come username {$user->user()->attribute( 'login' )}. Per impostare la password manda un messaggio con scritto #password, spazio e la password che vuoi impostare. Ad esempio \"#password 122345\"";
        $this->whatsProt->sendMessage( $user->whatsAppId(), $message );
    }

    protected function setPassword( SensorUserInfo $user, $body )
    {
        $newPassword = trim( str_replace( '#password ', '', trim( $body, '"' ) ) );
        if ( !empty( $newPassword ) )
        {
            $userAccount = $user->user();
            if ( eZOperationHandler::operationIsAvailable( 'user_password' ) )
            {
                eZOperationHandler::execute(
                    'user',
                    'password',
                    array(
                        'user_id'  => $userAccount->id(),
                        'new_password' => $newPassword
                    )
                );
            }
            else
            {
                eZUserOperationCollection::password( $userAccount->id(), $newPassword );
            }
            $this->whatsProt->sendMessage( $user->whatsAppId(), 'Password salvata!' );
        }
        else
        {
            $this->whatsProt->sendMessage( $user->whatsAppId(), 'Qualcosa non ha funzionato... Riprova!' );
        }

    }

    protected function createPost( SensorUserInfo $user, $data, $time )
    {
        if ( !$user->hasBlockMode() )
        {
            $object = false;
            $message = '';
            $updateLimitSeconds = self::UPDATE_LIMIT_SECONDS;
            $timeLeft = $updateLimitSeconds;
            $lastPost = SensorPostFetcher::fetchUserLastPost( $user );
            if ( $lastPost instanceof SensorHelper )
            {
                /** @var eZContentObject $lastObject */
                $lastObject = $lastPost->attribute( 'object' );

                $lastPostCreationDate = $lastObject->attribute( 'published' );
                $timeLeftCount = $time - $lastPostCreationDate;
                if ( $timeLeftCount <= $updateLimitSeconds )
                {
                    $timeLeft = $updateLimitSeconds - $timeLeftCount;
                    $object = SensorHelper::factory()->sensorPostObjectFactory(
                        $user,
                        $data,
                        $lastObject
                    );
                    $message .= "Segnalazione aggiornata";
                }
            }
            if ( !$object )
            {
                $object = SensorHelper::factory()->sensorPostObjectFactory( $user, $data );
                $message .= "Creata nuova segnalazione";
            }

            $helper = SensorHelper::instanceFromContentObjectId(
                $object->attribute( 'id' ),
                $user
            );
            $message .= ' ' . $helper->attribute( 'post_url' );
            if ( $timeLeft > 0 )
            {
                $message .= " (hai ancora $timeLeft secondi per aggiungere informazioni alla segnalazione)";
            }
            $this->whatsProt->sendMessage( $user->whatsAppId(), $message );
            $this->cli->warning( $object->attribute( 'id' ), false );
        }
    }

    protected function getUser( $from, $nickname )
    {
        $user = false;
        $username = str_replace( array( "@s.whatsapp.net", "@g.us" ), "", $from );
        $contentObject = OCWhatsAppType::fetchContentObjectByUsername( $username );
        if ( !$contentObject instanceof eZContentObject )
        {
            $sensorUserRegister = new SocialUserRegister();
            $sensorUserRegister->setName( $nickname );
            $sensorUserRegister->setEmail( $from );
            $contentObject = $sensorUserRegister->store();
            $module = new eZModule( null, null, 'sensor', false );
            SocialUserRegister::finish( $module, $contentObject );
            if ( $contentObject instanceof eZContentObject )
            {
                /** @var eZContentObjectAttribute[] $contentObjectDataMap */
                $contentObjectDataMap = $contentObject->attribute( 'data_map' );
                if ( isset( $contentObjectDataMap['wa_user'] ) )
                {
                    $contentObjectDataMap['wa_user']->fromString( $username . '|' . $nickname );
                    $contentObjectDataMap['wa_user']->store();
                }
                $user = SensorUserInfo::instance( eZUser::fetch( $contentObject->attribute( 'id' ) ) );
                $user->setModerationMode( true );
                $user->setDenyCommentMode( true );
                $message = 'Benvenut@! Per avere informazioni sul tuo account invia un messaggio con scritto "#help"';
                $this->whatsProt->sendMessage( $user->whatsAppId(), $message );
            }
        }
        else
        {
            $user = SensorUserInfo::instance( eZUser::fetch( $contentObject->attribute( 'id' ) ) );
        }
        return $user;
    }

    protected static function getRemoteFile( $url, array $httpAuth = null, $debug = false )
    {
        $url = trim( $url );
        $ini = eZINI::instance();
        $localPath = $ini->variable( 'FileSettings', 'TemporaryDir' ).'/'.basename( $url );
        $timeout = 50;

        $ch = curl_init( $url );
        $fp = fopen( $localPath, 'w+' );
        curl_setopt( $ch, CURLOPT_HEADER, false );
        curl_setopt( $ch, CURLOPT_FILE, $fp );
        curl_setopt( $ch, CURLOPT_TIMEOUT, (int)$timeout );
        curl_setopt( $ch, CURLOPT_FAILONERROR, true );
        if ( $debug )
        {
            curl_setopt( $ch, CURLOPT_VERBOSE, true );
            curl_setopt( $ch, CURLOPT_NOPROGRESS, false );
        }

        // Should we use proxy ?
        $proxy = $ini->variable( 'ProxySettings', 'ProxyServer' );
        if ( $proxy )
        {
            curl_setopt( $ch, CURLOPT_PROXY, $proxy );
            $userName = $ini->variable( 'ProxySettings', 'User' );
            $password = $ini->variable( 'ProxySettings', 'Password' );
            if ( $userName )
            {
                curl_setopt( $ch, CURLOPT_PROXYUSERPWD, "$username:$password" );
            }
        }

        // Should we use HTTP Authentication ?
        if( is_array( $httpAuth ) )
        {
            if( count( $httpAuth ) != 2 )
            {
                //throw new SQLIContentException( __METHOD__.' => HTTP Auth : Wrong parameter count in $httpAuth array' );
                return null;
            }

            list( $httpUser, $httpPassword ) = $httpAuth;
            curl_setopt( $ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY );
            curl_setopt( $ch, CURLOPT_USERPWD, $httpUser.':'.$httpPassword );
        }

        $result = curl_exec( $ch );
        if ( $result === false )
        {
            $error = curl_error( $ch );
            $errorNum = curl_errno( $ch );
            curl_close( $ch );
            //throw new SQLIContentException( "Failed downloading remote file '$url'. $error", $errorNum);
            return null;
        }

        curl_close( $ch );
        fclose( $fp );


        return trim($localPath);
    }
}