<?php

use Opencontent\Sensor\Api\Values\User;

class UserCsvImporter
{
    use MemberGroupsTrait;

    private static $availableHeaders = ['matricola', 'cognome', 'nome', 'codice_fiscale', 'email'];

    private static $separator = ';';

    private $file;

    private $fileHandler;

    protected $isParsed = false;

    private $headers = [];

    protected $values = [];

    public function __construct($file)
    {
        $this->fileHandler = eZClusterFileHandler::instance($file);
        $this->fileHandler->fetch();
        $this->file = $file;
    }

    public function validate()
    {
        $this->parse();

        if (count($this->values) > 0) {
            foreach (self::getAvaliableHeaders() as $avaliableHeader) {
                if (!in_array($avaliableHeader, $this->headers)) {
                    throw new Exception(
                        'Missing csv header `' . $avaliableHeader . '`. Headers must be `' .
                        implode('`, `', self::getAvaliableHeaders()) . '`'
                    );
                }
            }
        }
    }

    public function countValues()
    {
        $this->parse();

        return count($this->values);
    }

    public function import(eZContentObjectTreeNode $parentNode, $settings = [])
    {
        $this->parse();

        $settings = array_merge([
            'create_as' => 'user', //user or operator
        ], $settings);

        $repository = OpenPaSensorRepository::instance();

        foreach ($this->values as $value) {
            $user = false;

            if (!eZMail::validate($value['email']) || empty($value['codice_fiscale'])) {
                throw new Exception('Invalid user row: ' . var_export($value, true));
            }

            if (eZMail::validate($value['email'])) {
                $user = eZUser::fetchByEmail($value['email']);
            }

            if (!$user instanceof eZUser) {
                $user = eZUser::fetchByName($value['codice_fiscale']);
            }

            if (!$user instanceof eZUser) {
                if ($settings['create_as'] === 'user') {
                    $payload = [
                        'name' => $value['nome'],
                        'email' => $value['email'],
                        'fiscal_code' => $value['codice_fiscale'],
                    ];
                    $sensorUser = $repository->getUserService()->createUser($payload);
                } elseif ($settings['create_as'] === 'operator') {
                    $payload = [
                        'name' => $value['nome'] . ' ' . $value['cognome'],
                        'email' => $value['email'],
                    ];
                    $sensorUser = $repository->getOperatorService()->createOperator($payload);
                } else {
                    throw new Exception('Cannot handle user type ' . $settings['create_as']);
                }
            } else {
                $sensorUser = $repository->getUserService()->loadFromEzUser($user);
            }
            if (!$sensorUser instanceof User) {
                throw new Exception('Fail importing ' . $settings['create_as'], ': ' . var_export($value, true));
            } else {
                $this->user = eZUser::fetch($sensorUser->id);
                $this->addGroups([$parentNode->attribute('contentobject_id')]);
            }
        }
    }

    public function delayImport(eZContentObjectTreeNode $parentNode, $settings = [])
    {
        SensorBatchOperations::instance()->addPendingOperation(UserCsvImportHandler::SENSOR_HANDLER_IDENTIFIER, [
            'parent_node_id' => $parentNode->attribute('node_id'),
            'settings' => json_encode($settings),
            'file' => $this->file,
        ])->run();
    }

    public function cleanup()
    {
//        $this->fileHandler->deleteLocal();
//        $this->fileHandler->delete();
//        $this->fileHandler->purge();
    }

    /**
     * @return string[]
     */
    public static function getAvaliableHeaders()
    {
        return self::$availableHeaders;
    }

    /**
     * @return string
     */
    public static function getSeparator()
    {
        return self::$separator;
    }

    protected function parse()
    {
        if (!$this->isParsed) {
            $row = 1;
            if (($handle = fopen($this->file, "r")) !== false) {
                while (($data = fgetcsv($handle, 100000, self::getSeparator())) !== false) {
                    if ($row === 1) {
                        $this->headers = $data;
                    } else {
                        $value = [];
                        for ($j = 0, $jMax = count($this->headers); $j < $jMax; ++$j) {
                            $value[$this->headers[$j]] = $data[$j];
                        }
                        $this->values[] = $value;
                    }
                    $row++;
                }
                fclose($handle);
                $this->isParsed = true;
            } else {
                throw new Exception("File not found");
            }
        }
    }
}