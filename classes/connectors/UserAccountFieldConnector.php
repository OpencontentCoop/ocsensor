<?php

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

class UserAccountFieldConnector extends FieldConnector
{
    public function getSchema()
    {
        return array(
            "title" => \ezpI18n::tr('design/standard/content/datatype', 'Email'),
            "format" => "email",
            "type" => "string",
            'required' => (bool)$this->attribute->attribute('is_required')
        );
    }

    public function getOptions()
    {
        return array(
            "helper" => $this->attribute->attribute('description'),
            "autocomplete" => 'off',
        );
    }

    public function setPayload($email)
    {
        // workaround per permettere modifica email
        if ($this->getHelper()->hasParameter('object') && eZMail::validate($email)) {
            $user = eZUser::fetch((int)$this->getHelper()->getParameter('object'));
            if ($user instanceof eZUser && $user->attribute('email') !== $email) {
                if (eZUser::fetchByEmail($email) instanceof eZUser) {
                    throw new Exception("Indirizzo email già in uso", 1);
                } else {
                    $user->setAttribute('email', $email);
                    $userObject = eZContentObject::fetch($user->id());
                    if ($userObject instanceof eZContentObject) {
                        foreach ($userObject->attribute('contentobject_attributes') as $contentObjectAttribute) {
                            if ($contentObjectAttribute->attribute('data_type_string') === 'ezuser') {
                                $contentObjectAttribute->setAttribute('data_text', $this->serializeDraft($user));
                                $user->store();
                                $contentObjectAttribute->store();
                            }
                        }
                    }
                }
            }
        }elseif (eZUser::fetchByEmail($email) instanceof eZUser) {
            throw new Exception("Indirizzo email già in uso", 1);
        }

        $postData = [];
        $postData['email'] = $email;
        if ($this->getHelper()->hasParameter('object')) {
            $postData['id'] = $this->getHelper()->getParameter('object');
        }else{
            $postData['login'] = $email;
        }

        return $postData;
    }

    private function serializeDraft(eZUser $user)
    {
        return json_encode(
            array(
                'login' => $user->attribute('login'),
                'password_hash' => $user->attribute('password_hash'),
                'email' => $user->attribute('email'),
                'password_hash_type' => $user->attribute('password_hash_type')
            )
        );
    }
}