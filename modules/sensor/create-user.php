<?php

$http = eZHTTPTool::instance();

$response = [];
$db = eZDB::instance();
try {
    if ($http->hasPostVariable('first_name') && trim($http->postVariable('first_name')) != '') {
        $firstName = trim($http->postVariable('first_name'));
    } else {
        throw new Exception("First name is required");
    }

    if ($http->hasPostVariable('last_name') && trim($http->postVariable('last_name')) != '') {
        $lastName = trim($http->postVariable('last_name'));
    } else {
        throw new Exception("Last name is required");
    }

    if ($http->hasPostVariable('fiscal_code') && trim($http->postVariable('fiscal_code')) != '') {
        $fiscalCode = trim($http->postVariable('fiscal_code'));
    } else {
        throw new Exception("Fiscal code is required");
    }

    if ($http->hasPostVariable('email') && trim($http->postVariable('email')) != '') {
        $email = trim($http->postVariable('email'));
    }else{
        $email = $fiscalCode . '@invalid.email';
    }

    $ini = eZINI::instance();
    $userClassID = $ini->variable("UserSettings", "UserClassID");
    $defaultUserPlacement = (int)$ini->variable("UserSettings", "DefaultUserPlacement");
    $sql = "SELECT count(*) as count FROM ezcontentobject_tree WHERE node_id = $defaultUserPlacement";
    $rows = $db->arrayQuery($sql);
    $count = $rows[0]['count'];
    if ($count < 1) {
        throw new Exception(
            ezpI18n::tr(
                'design/standard/user',
                'The node (%1) specified in [UserSettings].DefaultUserPlacement setting in site.ini does not exist!', null, array($defaultUserPlacement)
            )
        );
    }
    $defaultSectionID = $ini->variable("UserSettings", "DefaultSectionID");
    if ($defaultSectionID == 0 && $count > 0) {
        $parentContentObject = eZContentObject::fetchByNodeID($defaultUserPlacement);
        $defaultSectionID = $parentContentObject->attribute('section_id');
    }
    $contentClass = eZContentClass::fetch($userClassID);
    $creatorID = eZUser::currentUserID();

    if ($contentClass instanceof eZContentClass) {
        $db->begin();

        $languageCode = isset($params['language']) ? $params['language'] : false;
        $sectionID = isset($params['section_id']) ? $params['section_id'] : 0;
        $contentObject = $contentClass->instantiate($creatorID, $sectionID, false, $languageCode);
        $contentObject->store();

        $nodeAssignment = eZNodeAssignment::create(array('contentobject_id' => $contentObject->attribute('id'),
            'contentobject_version' => $contentObject->attribute('current_version'),
            'parent_node' => $defaultUserPlacement,
            'is_main' => 1,
            'sort_field' => $contentClass->attribute('sort_field'),
            'sort_order' => $contentClass->attribute('sort_order')));
        $nodeAssignment->store();

        $version = $contentObject->version(1);
        $version->setAttribute('modified', eZDateTime::currentTimeStamp());
        $version->setAttribute('status', eZContentObjectVersion::STATUS_DRAFT);
        $version->store();

        /** @var eZContentObjectAttribute[] $attributes */
        $attributes = $contentObject->attribute('contentobject_attributes');
        foreach ($attributes as $attribute) {
            $attributeIdentifier = $attribute->attribute('contentclass_attribute_identifier');
            if ($attributeIdentifier == 'first_name'){
                $attribute->fromString($firstName);
                $attribute->store();
            }elseif ($attributeIdentifier == 'last_name'){
                $attribute->fromString($lastName);
                $attribute->store();
            }elseif ($attributeIdentifier == 'fiscal_code'){
                if (!$attribute->fromString($fiscalCode)){
                    throw new InvalidArgumentException("Fiscal code is not valid");
                }
                $attribute->store();
            }elseif ($attributeIdentifier == 'user_account'){
                $hash = eZUser::passwordHashTypeName(eZUser::hashType());
                $account = "{$fiscalCode}|{$email}||{$hash}|1";
                if (!$attribute->fromString($account)){
                    throw new InvalidArgumentException("Email {$email} already exists");
                }
                $attribute->store();
            }

        }

        $db->commit();

        $operationResult = eZOperationHandler::execute('content', 'publish', array(
            'object_id' => $contentObject->attribute('id'),
            'version' => 1
        ));

        $response = \Opencontent\Opendata\Api\Values\Content::createFromEzContentObject($contentObject);

    } else {
        throw new Exception("User class not found", __METHOD__);
    }

} catch (InvalidArgumentException $e) {
    $response['error'] = $e->getMessage();
    $db->rollback();
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);

#eZDisplayDebug();
eZExecution::cleanExit();