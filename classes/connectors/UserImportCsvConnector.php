<?php

use Opencontent\Ocopendata\Forms\Connectors\AbstractBaseConnector;
use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\CleanableFieldConnectorInterface;

class UserImportCsvConnector extends AbstractBaseConnector implements CleanableFieldConnectorInterface
{
    const DELAY_IMPORT_MIN_ITEMS = 50;

    /**
     * @var eZContentObjectTreeNode
     */
    private $parentNode;

    private $userTypes = [
        'user' => 'Utente',
        'operator' => 'Operatore',
    ];

    protected function load()
    {
        if ($this->parentNode === null) {
            $this->parentNode = eZContentObjectTreeNode::fetch(
                (int)$this->getHelper()->getParameter('id')
            );
            if (!$this->parentNode instanceof eZContentObjectTreeNode) {
                throw new Exception('Parent node not found');
            }
        }
    }

    public function runService($serviceIdentifier)
    {
        $this->load();
        if (($serviceIdentifier == 'action' || $serviceIdentifier == 'upload') && !$this->parentNode->canCreate()) {
            throw new Exception('Can not create contents in current parent node');
        }

        return parent::runService($serviceIdentifier);
    }

    protected function getData()
    {
        return null;
    }

    protected function getSchema()
    {
        return [
            'title' => 'Assegna utenti al gruppo ' . $this->parentNode->attribute('name') . ' da file CSV',
            'type' => 'object',
            'properties' => [
                'file' => [],
                'create_as' => [
                    'enum' => array_keys($this->userTypes),
                    'title' => 'Crea nuovi utenti come...',
                ],
            ],
        ];
    }

    protected function getOptions()
    {
        $options = [
            'form' => [
                'attributes' => [
                    'action' => $this->getHelper()->getServiceUrl('action', $this->getHelper()->getParameters()),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ],
            'helper' => 'Carica un file CSV con la prima riga di intestazione (<code>'. implode('</code>, <code>', UserCsvImporter::getAvaliableHeaders()) .'</code>).<br /> Il separatore è il carattere <code>' . UserCsvImporter::getSeparator() . '</code>',
            'fields' => [
                'file' => [
                    'type' => 'upload',
                    'upload' => [
                        'url' => $this->getHelper()->getServiceUrl('upload', $this->getHelper()->getParameters()),
                        'autoUpload' => true,
                        'showSubmitButton' => false,
                        'disableImagePreview' => true,
                        'maxFileSize' => 25000000, //@todo,
                        'maxNumberOfFiles' => 1,
                    ],
                    'showUploadPreview' => false,
                    'maxNumberOfFiles' => 1,
                    'multiple' => false,
                ],
                'create_as' => [
                    'optionLabels' => array_values($this->userTypes),
                    "type" => 'select',
                    'multiple' => false,
                    'hideNone' => true,
                    'sort' => false,
                ]
            ],
        ];

        return $options;
    }

    protected function getView()
    {
        $parent = 'bootstrap-create';
        if ($this->getData() !== null) {
            $parent = 'bootstrap-edit';
        }

        return [
            'parent' => $parent,
            'locale' => $this->getAlpacaLocale(),
        ];
    }

    protected function getAlpacaLocale()
    {
        $localeMap = [
            'eng-GB' => false,
            'chi-CN' => 'zh_CN',
            'cze-CZ' => 'cs_CZ',
            'cro-HR' => 'hr_HR',
            'dut-NL' => 'nl_BE',
            'fin-FI' => 'fi_FI',
            'fre-FR' => 'fr_FR',
            'ger-DE' => 'de_DE',
            'ell-GR' => 'el_GR',
            'ita-IT' => 'it_IT',
            'jpn-JP' => 'ja_JP',
            'nor-NO' => 'nb_NO',
            'pol-PL' => 'pl_PL',
            'por-BR' => 'pt_BR',
            'esl-ES' => 'es_ES',
            'swe-SE' => 'sv_SE',
        ];

        $currentLanguage = $this->getHelper()->getSetting('language');

        return isset($localeMap[$currentLanguage]) ? $localeMap[$currentLanguage] : 'it_IT';
    }

    protected function submit()
    {
        $data = $_POST;

        if (isset($data['file'])) {
            $dataFile = array_pop($data['file']);
            $file = $this->getUploadDir() . $dataFile['name'];
            $importer = new UserCsvImporter($file);
            $importer->validate();
            $settings = [
                'create_as' => isset($data['create_as']) ? $data['create_as'] : 'user',
            ];
            if ($importer->countValues() > self::DELAY_IMPORT_MIN_ITEMS) {
                $importer->delayImport($this->parentNode, $settings);
            } else {
                $importer->import($this->parentNode, $settings);
                $importer->cleanup();
            }
        }

        return true;
    }

    protected function getUploadDir()
    {
        $directory = md5(\eZUser::currentUserID() . $this->parentNode->attribute('node_id'));

        $uploadDir = eZSys::storageDirectory() . '/fileupload/' . $directory . '/';
        \eZDir::mkdir($uploadDir, false, true);

        return $uploadDir;
    }

    protected function upload()
    {
        $paramName = 'file_files';

        if ($this->getHelper()->hasParameter('delete')) {
            return $this->delete();
        }

        $options = [];
        $options['upload_dir'] = $this->getUploadDir();
        $options['download_via_php'] = true;
        $options['param_name'] = $paramName;

        $uploadHandler = new UploadHandler($options, false);
        $data = $uploadHandler->post(false);

        $files = [];
        foreach ($data[$options['param_name']] as $file) {
            $tempFileCheck = file_exists($this->getUploadDir() . $file->name);
            \eZClusterFileHandler::instance()->fileStore(
                $this->getUploadDir() . $file->name,
                'binaryfile',
                true,
                'application/csv'
            );
            $parameters = $this->getHelper()->getParameters();
            $parameters['delete'] = $file->name;
            $files[] = [
                'id' => uniqid($file->name),
                'name' => $file->name,
                'size' => $file->size,
                'url' => '#',
                'thumbnailUrl' => false,
                'deleteUrl' => $this->getHelper()->getServiceUrl('upload', $parameters),
                'deleteType' => "GET",
                'tempFileCheck' => $tempFileCheck,
            ];
        }

        return ['files' => $files];
    }

    private function delete()
    {
        $fileName = $this->getHelper()->getParameter('delete');

        $filePath = $this->getUploadDir() . $fileName;
        $file = \eZClusterFileHandler::instance($filePath);
        if ($file->exists()) {
            $file->delete();
        }

        return array(
            'files' => array(
                array(
                    $fileName => true
                )
            )
        );
    }

    public function cleanup()
    {
        // TODO: Implement cleanup() method.
    }

}