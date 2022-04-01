<?php

use Opencontent\Ocopendata\Forms\Connectors\AbstractBaseConnector;

class DuplicatePostConnector extends AbstractBaseConnector
{
    protected $isLoaded;

    /**
     * @var eZContentObject
     */
    protected $source;

    /**
     * @var eZContentObjectAttribute[]
     */
    protected $sourceDataMap;

    /**
     * @var OpenPaSensorRepository
     */
    protected $repository;

    /**
     * @var eZContentClassAttribute
     */
    protected $descriptionAttribute;

    /**
     * @var eZContentClassAttribute
     */
    protected $subjectAttribute;

    protected function load()
    {
        if (!$this->isLoaded) {
            $this->repository = OpenPaSensorRepository::instance();
            $this->descriptionAttribute = $this->repository->getPostContentClassAttribute('description');
            $this->subjectAttribute = $this->repository->getPostContentClassAttribute('subject');
            if ($this->hasParameter('source')) {
                $this->source = eZContentObject::fetch((int)$this->getParameter('source'));
                if (!$this->source instanceof eZContentObject){
                    throw new Exception('Not found');
                }
                if (!$this->repository->getCurrentUser()->behalfOfMode){
                    throw new Exception('Forbidden');
                }
                $this->sourceDataMap = $this->source->dataMap();
            }
            $this->isLoaded = true;
        }
    }

    public function runService($serviceIdentifier)
    {
        $this->load();
        return parent::runService($serviceIdentifier);
    }

    protected function getData()
    {
        $data = $this->sourceDataMap['description']->toString();
        $data = preg_replace('/__(.+?)__/s', "<strong>$1</strong>", $data);
        $data = nl2br($data);

        return [
            'subject' => $this->sourceDataMap['subject']->toString(),
            'description' => $data,
        ];
    }

    protected function getSchema()
    {
        return [
            "title" => "Duplica segnalazione",
            "type" => "object",
            "properties" => [
                "subject" => [
                    "type" => "string",
                    "title" => $this->subjectAttribute->attribute('name'),
                    'required' => true
                ],
                "description" => [
                    "type" => "string",
                    "title" => $this->descriptionAttribute->attribute('name'),
                    'required' => true
                ],
                "redirect" => array(
                    "type" => "boolean",
                    'default' => true,
                ),
            ],
        ];
    }

    protected function getOptions()
    {
        return [
            "form" => [
                "attributes" => [
                    "action" => $this->getHelper()->getServiceUrl('action', $this->getHelper()->getParameters()),
                    "method" => "post",
                ],
                "buttons" => [
                    "submit" => [],
                ],
            ],
            "fields" => [
                "description" => [
                    "helper" => $this->descriptionAttribute->attribute('description'),
//                    "type" => "textarea",
                    "type" => "summernote",
                    "summernote" => array(
                        "toolbar" => array (
                            array('style', array('bold', 'clear'))
                        )
                    ),
                ],
                "redirect" => array(
                    "type" => "checkbox",
                    "rightLabel" => 'Al salvataggio reindirizza alla segnalazione duplicata',
                ),
            ],
        ];
    }

    protected function getView()
    {
        return [
            "parent" => "bootstrap-edit",
            "locale" => "it_IT",
        ];
    }

    protected function submit()
    {
        $redirect = $_POST['redirect'] === 'true';
        $newSubject = $_POST['subject'];
        $newDescription = $_POST['description'];
        $newDescription = preg_replace('#<br\s*/?>#i', "", $newDescription);
        $newDescription = str_replace(['<b>', '</b>'], "__", $newDescription);

        $copy = eZContentFunctions::createAndPublishObject([
            'parent_node_id' => $this->repository->getPostRootNode()->attribute('node_id'),
            'class_identifier' => $this->repository->getPostContentClass()->attribute('identifier'),
            'attributes' => [
                'reporter' => eZUser::currentUserID(),
                'on_behalf_of' => $this->source->attribute('owner_id'),
                'subject' => $newSubject,
                'type' => $this->sourceDataMap['type']->toString(),
                'image' => $this->sourceDataMap['image']->toString(),
                'images' => $this->sourceDataMap['images']->toString(),
                'privacy' => $this->sourceDataMap['privacy']->toString(),
                'description' => $newDescription,
                'on_behalf_of_detail' => $this->sourceDataMap['on_behalf_of_detail']->toString(),
                'on_behalf_of_mode' => $this->sourceDataMap['on_behalf_of_mode']->toString(),
                'meta' => $this->sourceDataMap['meta']->toString(),
                'geo' => $this->sourceDataMap['geo']->toString(),
                'area' => $this->sourceDataMap['area']->toString(),
            ]
        ]);

        if ($copy instanceof eZContentObject){
            $this->source->addContentObjectRelation($copy->attribute('id'));
            return $redirect ? $copy->attribute('id') : false;
        }

        throw new Exception('Error duplicating post');
    }

    protected function upload()
    {
        return false;
    }

}
