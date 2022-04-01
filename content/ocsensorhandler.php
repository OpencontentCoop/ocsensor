<?php

class ocsensorhandler extends eZContentObjectEditHandler
{
    const PASSWORD_FORGOT_MESSAGE = "To recover the access password, visit the page \n %s";

    const PENDING_USER_DUPLICATED_FISCAL_CODE_MESSAGE = "There is already a registration pending activation for the '%s' fiscal code";

    const NOTIFICATION_SENT_MESSAGE = "The activation email has been sent again: check the '%s' email address";

    const DUPLICATED_LOGIN_MESSAGE = "The '%s' username already exists, please choose another one";

    /**
     * @param eZHTTPTool $http
     * @param eZModule $module
     * @param eZContentClass $class
     * @param eZContentObject $object
     * @param eZContentObjectVersion $version
     * @param eZContentObjectAttribute[] $contentObjectAttributes
     * @param int $editVersion
     * @param string $editLanguage
     * @param string $fromLanguage
     * @param array $validationParameters
     * @return array
     */
    function validateInput($http, &$module, &$class, $object, &$version, $contentObjectAttributes, $editVersion, $editLanguage, $fromLanguage, $validationParameters)
    {
        $base = 'ContentObjectAttribute';
        $result = parent::validateInput($http, $module, $class, $object, $version, $contentObjectAttributes, $editVersion, $editLanguage, $fromLanguage, $validationParameters);

        if ($module->currentModule() === 'user' && $module->currentView() === 'register') {
            $forgotPasswordPage = '/user/forgotpassword';
            if (eZModule::exists('userpaex')){
                $forgotPasswordPage = 'userpaex/forgotpassword';
            }
            eZURI::transformURI($forgotPasswordPage, false, 'full');
            $addForgotLink = false;
            $hasNotificationSent = false;

            $userClass = eZContentClass::fetch(eZINI::instance()->variable("UserSettings", "UserClassID"));
            $fiscalCodeAttribute = false;
            $accountAttribute = false;
            /**
             * @var string $identifier
             * @var eZContentClassAttribute $classAttribute
             */
            foreach ($userClass->dataMap() as $identifier => $classAttribute) {
                if ($classAttribute->attribute('data_type_string') === OCCodiceFiscaleType::DATA_TYPE_STRING) {
                    $fiscalCodeAttribute = $classAttribute;
                }
                if ($classAttribute->attribute('data_type_string') === eZUserType::DATA_TYPE_STRING) {
                    $accountAttribute = $classAttribute;
                }
            }
            if ($fiscalCodeAttribute) {
                $fiscalCodePostVarName = false;
                foreach ($contentObjectAttributes as $contentObjectAttribute) {
                    if ($contentObjectAttribute->attribute('contentclassattribute_id') == $fiscalCodeAttribute->attribute('id')) {
                        $fiscalCodePostVarName = "{$base}_ezstring_data_text_" . $contentObjectAttribute->attribute('id');
                        break;
                    }
                }
                if ($fiscalCodePostVarName && $http->hasPostVariable($fiscalCodePostVarName)) {
                    $fiscalCode = strtoupper($http->postVariable($fiscalCodePostVarName));
                    $duplicatedObjectList = $this->fetchObjectByFiscalCode($fiscalCode, $fiscalCodeAttribute->attribute('id'), $object->attribute('id'));

                    $hasDraft = false;
                    $hasPublished = false;
                    foreach ($duplicatedObjectList as $duplicatedObject) {
                        if ($duplicatedObject->currentVersion()->attribute('status') == eZContentObjectVersion::STATUS_DRAFT) {
                            $hasDraft = $duplicatedObject;
                        }
                        if ($duplicatedObject->currentVersion()->attribute('status') == eZContentObjectVersion::STATUS_INTERNAL_DRAFT) {
                            $duplicatedObject->currentVersion()->removeThis();
                        }
                        if ($duplicatedObject->currentVersion()->attribute('status') == eZContentObjectVersion::STATUS_PUBLISHED) {
                            $hasPublished = $duplicatedObject;
                        }
                    }

                    if ($hasPublished) {
                        $result['is_valid'] = false;
                        $addForgotLink = true;
                    } elseif ($hasDraft) {
                        $pendingUser = eZUser::fetch($hasDraft->attribute('id'));
                        if ($pendingUser instanceof eZUser) {
                            $errorText = sprintf(self::PENDING_USER_DUPLICATED_FISCAL_CODE_MESSAGE, $fiscalCode);
                            $verifyUserType = eZINI::instance()->variable('UserSettings', 'VerifyUserType');
                            if ($verifyUserType === 'email') {
                                eZUserOperationCollection::sendActivationEmail($hasDraft->attribute('id'));
                                $errorText .= "\n" . sprintf(self::NOTIFICATION_SENT_MESSAGE, $this->obfuscateEmail($pendingUser->attribute('email')));
                                $hasNotificationSent = true;
                            }
                            $result['is_valid'] = false;
                            $result['warnings'][] = ['text' => $errorText];
                        } else {
                            $hasDraft->removeThis();
                        }
                    }
                }
            }
            if ($accountAttribute){
                $emailPostVarName = false;
                foreach ($contentObjectAttributes as $contentObjectAttribute) {
                    if ($contentObjectAttribute->attribute('contentclassattribute_id') == $accountAttribute->attribute('id')) {
                        $emailPostVarName = "{$base}_data_user_email_" . $contentObjectAttribute->attribute('id');
                        break;
                    }
                }
                if ($emailPostVarName && $http->hasPostVariable($emailPostVarName)) {
                    $email = $http->postVariable($emailPostVarName);
                    $alreadyExistsEmail = eZUser::fetchByEmail($email);
                    if ($alreadyExistsEmail instanceof eZUser && $alreadyExistsEmail->id() != $object->attribute('id')){
                        $result['is_valid'] = false;
                        $addForgotLink = true;
                    }
                }
                $loginPostVarName = false;
                foreach ($contentObjectAttributes as $contentObjectAttribute) {
                    if ($contentObjectAttribute->attribute('contentclassattribute_id') == $accountAttribute->attribute('id')) {
                        $loginPostVarName = "{$base}_data_user_login_" . $contentObjectAttribute->attribute('id');
                        break;
                    }
                }
                if ($loginPostVarName && $http->hasPostVariable($loginPostVarName)) {
                    $login = $http->postVariable($loginPostVarName);
                    $alreadyExistsLogin = eZUser::fetchByName($login);
                    if ($alreadyExistsLogin instanceof eZUser && $alreadyExistsLogin->id() != $object->attribute('id')){
                        $result['is_valid'] = false;
                        $result['warnings'][] = ['text' => sprintf(self::DUPLICATED_LOGIN_MESSAGE, $login)];
                        $_POST[$loginPostVarName] = '';
                    }
                }
            }

            if ($addForgotLink && !$hasNotificationSent){
                $result['warnings'][] = ['text' => sprintf(self::PASSWORD_FORGOT_MESSAGE, $forgotPasswordPage)];
            }
        }

        return $result;
    }

    /**
     * @param $fiscalCode
     * @param $contentClassAttributeID
     * @param $contentObjectID
     * @return eZContentObject[]
     */
    private function fetchObjectByFiscalCode($fiscalCode, $contentClassAttributeID, $contentObjectID)
    {
        $fiscalCode = trim($fiscalCode);
        if (!empty($fiscalCode)) {
            $query = "SELECT co.id
				FROM ezcontentobject co, ezcontentobject_attribute coa
				WHERE co.id = coa.contentobject_id
				AND co.current_version = coa.version								
				AND co.id != " . intval($contentObjectID) . "
                AND coa.contentclassattribute_id = " . intval($contentClassAttributeID) . "
				AND UPPER(coa.data_text) = '" . eZDB::instance()->escapeString(strtoupper($fiscalCode)) . "'
				ORDER BY co.published ASC";

            $result = eZDB::instance()->arrayQuery($query);
            if (isset($result[0]['id'])) {
                $idList = array_column($result, 'id');
                return OpenPABase::fetchObjects($idList);
            }
        }
        return [];
    }

    private function obfuscateEmail($email)
    {
        $em = explode("@", $email);
        $name = implode('@', array_slice($em, 0, count($em) - 1));
        $len = floor(strlen($name) / 2);

        return substr($name, 0, $len) . str_repeat('*', $len) . "@" . end($em);
    }

}
