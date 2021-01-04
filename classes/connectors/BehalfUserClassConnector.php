<?php

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\ClassConnector;
use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnectorFactory;
use Opencontent\Opendata\Api\ContentRepository;
use Opencontent\Opendata\Api\EnvironmentLoader;
use Opencontent\Opendata\Rest\Client\PayloadBuilder;

class BehalfUserClassConnector extends ClassConnector
{
    public function getFieldConnectors()
    {
        if ($this->fieldConnectors === null) {
            /** @var \eZContentClassAttribute[] $classDataMap */
            $classDataMap = $this->class->dataMap();
            $defaultCategory = \eZINI::instance('content.ini')->variable('ClassAttributeSettings', 'DefaultCategory');
            foreach ($classDataMap as $identifier => $attribute) {

                $category = $attribute->attribute('category');
                if (empty( $category )) {
                    $category = $defaultCategory;
                }

                $add = true;
                if (in_array($identifier, ['gdpr_acceptance', 'antispam'])) {
                    continue;
                }

                if (in_array($identifier, ['password_lifetime'])) {
                    $add = false;
                }

                if ($add && !in_array($category, ['hidden'])) {
                    $this->fieldConnectors[$identifier] = FieldConnectorFactory::load(
                        $attribute,
                        $this->class,
                        $this->getHelper()
                    );
                } else {
                    $this->copyFieldFromPrevVersion($identifier);
                }
            }
        }

        return $this->fieldConnectors;
    }

    public function getSchema()
    {
        $schema = parent::getSchema();
        $schema['title'] = '';
        $schema['description'] = '';
        $schema['properties']['user_account']['required'] = false;

        return $schema;
    }

    public function submit()
    {
        $submitData = $this->getSubmitData();
        $payload = $this->getPayloadFromArray($submitData);
        $payload->setData(null, 'gdpr_acceptance', '1');
        $payload->setData(null, 'antispam', 'ok');

        $fiscalCode = isset($submitData['fiscal_code']) ? $submitData['fiscal_code'] : null;
        $codiceFiscale = new CodiceFiscale();
        $codiceFiscale->SetCF($fiscalCode);

        if (empty($payload->getData('user_account'))) {
            $firstName = $submitData['first_name'];
            $lastName = $submitData['last_name'];
            $nameNormalized = eZCharTransform::instance()->transformByGroup("{$firstName}.{$lastName}", 'identifier');
            $email = $login = $nameNormalized . '@invalid.email';

            $user = eZUser::fetchByEmail($email);
            if ($user instanceof eZUser && $user->contentObject() instanceof eZContentObject) {
                return array(
                    'message' => 'success',
                    'method' => 'already-exists',
                    'content' => (array)\Opencontent\Opendata\Api\Values\Content::createFromEzContentObject($user->contentObject())
                );
            }

            if ($fiscalCode && $codiceFiscale->GetCodiceValido()) {
                $user = eZUser::fetchByName($fiscalCode);
                if ($user instanceof eZUser && $user->contentObject() instanceof eZContentObject) {
                    return array(
                        'message' => 'success',
                        'method' => 'already-exists',
                        'content' => (array)\Opencontent\Opendata\Api\Values\Content::createFromEzContentObject($user->contentObject())
                    );
                }
                $login = $fiscalCode;
            }

            $payload->setData(null, 'user_account', ['login' => $login, 'email' => $email]);

        } elseif ($fiscalCode && $codiceFiscale->GetCodiceValido()) {
            $account = $payload->getData('user_account', $this->getHelper()->getSetting('language'));
            $account['login'] = $fiscalCode;
            $payload->setData(null, 'user_account', $account);
        }

        return $this->doSubmit($payload);
    }

    protected function doSubmit(PayloadBuilder $payload)
    {
        $contentRepository = new ContentRepository();
        $contentRepository->setEnvironment(EnvironmentLoader::loadPreset('content'));

        if ($this->isUpdate()) {
            $result = $contentRepository->update($payload->getArrayCopy(), true);
        } else {
            $result = $contentRepository->create($payload->getArrayCopy(), true);
        }

        $this->cleanup();

        return $result;
    }
}