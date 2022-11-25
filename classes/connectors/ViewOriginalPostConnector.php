<?php

use Opencontent\Ocopendata\Forms\Connectors\AbstractBaseConnector;

class ViewOriginalPostConnector extends AbstractBaseConnector
{
    protected $isLoaded;

    /**
     * @var eZContentObject
     */
    protected $post;

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

    /**
     * @var eZContentClassAttribute
     */
    protected $imagesAttribute;

    /**
     * @var eZContentClassAttribute
     */
    protected $filesAttribute;

    protected function load()
    {
        if (!$this->isLoaded) {
            $this->repository = OpenPaSensorRepository::instance();
            $this->descriptionAttribute = $this->repository->getPostContentClassAttribute('description');
            $this->subjectAttribute = $this->repository->getPostContentClassAttribute('subject');
            $this->imagesAttribute = $this->repository->getPostContentClassAttribute('images');
            $this->filesAttribute = $this->repository->getPostContentClassAttribute('files');
            if ($this->hasParameter('object')) {
                $this->post = eZContentObject::fetch((int)$this->getParameter('object'));
                if (!$this->post instanceof eZContentObject) {
                    throw new Exception('Not found');
                }
                if (!($this->post->canRead() && $this->post->attribute('owner_id') == eZUser::currentUserID())) {
                    throw new Exception('Forbidden');
                }
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
        $version = $this->post->version(1);
        $dataMap = $version->dataMap();
        return [
            'subject' => $dataMap['subject']->toString(),
            'description' => $dataMap['description']->toString(),
            'images' => $this->imagesAttribute ? $this->formatMultiFile($dataMap['images']) : null,
            'files' => $this->filesAttribute ? $this->formatMultiFile($dataMap['files']) : null,
        ];
    }

    private function formatMultiFile(eZContentObjectAttribute $attribute)
    {
        $files = [];
        if ($attribute->attribute('data_type_string') === OCMultiBinaryType::DATA_TYPE_STRING){
            /** @var eZMultiBinaryFile[] $content */
            $content = $attribute->content();
            foreach ($content as $item){
                $files[] = $item->attribute('original_filename');
            }
        }
        return $files;
    }

    protected function getSchema()
    {
        $schema = [
            "title" => SensorTranslationHelper::instance()->translate('Versione originale'),
            "type" => "object",
            "properties" => [
                "subject" => [
                    "type" => "string",
                    "title" => $this->subjectAttribute->attribute('name'),
                ],
                "description" => [
                    "type" => "string",
                    "title" => $this->descriptionAttribute->attribute('name'),
                ],

            ],
        ];
        if ($this->imagesAttribute){
            $schema['properties']['images'] = [
                "type" => "array",
                "items" => [
                    "type" => "string",
                ],
                "title" => $this->imagesAttribute->attribute('name'),
            ];
        }
        if ($this->filesAttribute){
            $schema['properties']['files'] = [
                "type" => "array",
                "items" => [
                    "type" => "string",
                ],
                "title" => $this->filesAttribute->attribute('name'),
            ];
        }

        return $schema;
    }

    protected function getOptions()
    {
        return [];
    }

    protected function getView()
    {
        return [
            "parent" => "bootstrap-display",
            "locale" => "it_IT",
        ];
    }

    protected function submit()
    {
        return false;
    }

    protected function upload()
    {
        return false;
    }

}
