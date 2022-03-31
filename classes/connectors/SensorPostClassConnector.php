<?php

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\ClassConnector;

class SensorPostClassConnector extends ClassConnector
{
    public function getSchema()
    {
        $schema = parent::getSchema();
        $schema['title'] = SensorTranslationHelper::instance()->translate('Create issue', 'menu');
        $schema['description'] = '';

        return $schema;
    }

    public function getOptions()
    {
        $options = parent::getOptions();
        $options['helper'] = '';

        return $options;
    }


    public function getView()
    {
        $view = parent::getView();

        $view['messages'] = [
            'it_IT' => [
                "chooseFile" => "Seleziona un file...",
                "chooseFiles" => "Seleziona uno o più file...",
                "dropZoneSingle" => "Clicca su seleziona o trascina qui per caricare file...",
                "dropZoneMultiple" => "Clicca su seleziona o trascina qui per caricare file...",
            ],
            'de_DE' => [
                "chooseFile" => SensorTranslationHelper::instance()->translate("Choose File..."),
                "chooseFiles" => SensorTranslationHelper::instance()->translate("Choose Files..."),
                "dropZoneSingle" => SensorTranslationHelper::instance()->translate("Click the Choose button or Drag and Drop a file here to upload..."),
                "dropZoneMultiple" => SensorTranslationHelper::instance()->translate("Click the Choose button or Drag and Drop files here to upload..."),
            ],
        ];

        return $view;
    }

}
