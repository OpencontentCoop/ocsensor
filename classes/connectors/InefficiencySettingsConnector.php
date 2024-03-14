<?php

use Opencontent\Ocopendata\Forms\Connectors\AbstractBaseConnector;
use Opencontent\Stanzadelcittadino\Client;

class InefficiencySettingsConnector extends AbstractBaseConnector
{
    private $types = [];

    private $inefficiencySettings = [];

    public function __construct($identifier)
    {
        $this->types = OpenPaSensorRepository::instance()->getPostTypeService()->loadPostTypes();
        $this->inefficiencySettings = OpenPaSensorRepository::instance()->getSensorSettings()->get('Inefficiency');
        parent::__construct($identifier);
    }

    protected function getData()
    {
        return [
            'is_enabled' => $this->inefficiencySettings->is_enabled,
            'base_url' => $this->inefficiencySettings->base_url,
            'api_login' => $this->inefficiencySettings->api_login,
            'api_password' => !empty($this->inefficiencySettings->api_password) ? 'EZ_PASSWORD' : '',
            'default_group_name' => $this->inefficiencySettings->default_group_name,
            'service_identifier' => $this->inefficiencySettings->service_identifier,
            'service_slug' => $this->inefficiencySettings->service_slug,
            'severity_1' => $this->inefficiencySettings->severity_map[1],
            'severity_2' => $this->inefficiencySettings->severity_map[2],
            'severity_3' => $this->inefficiencySettings->severity_map[3],
            'severity_4' => $this->inefficiencySettings->severity_map[4],
            'severity_5' => $this->inefficiencySettings->severity_map[5],
        ];
    }

    protected function getSchema()
    {
        return [
            'type' => 'object',
            'title' => 'Segnalazioni disservizio di Area personale',
            'properties' => [
                'is_enabled' => [
                    'type' => 'boolean',
                ],
                'base_url' => [
                    'type' => 'string',
                    'title' => 'Url Area personale',
                    'format' => 'uri',
                    'required' => true,
                ],
                'api_login' => [
                    'type' => 'string',
                    'title' => 'User name operatore Api',
                    'required' => true,
                ],
                'api_password' => [
                    'type' => 'string',
                    'format' => 'password',
                    'title' => 'Password operatore Api',
                    'required' => true,
                ],
                'default_group_name' => [
                    'type' => 'string',
                    'title' => 'Nome del ufficio responsabile',
                    'default' => 'Ufficio Relazioni con il pubblico',
                    'required' => true,
                ],
                'service_identifier' => [
                    'type' => 'string',
                    'title' => 'Identificatore del servizio',
                    'default' => 'inefficiencies',
                    'required' => true,
                ],
                'service_slug' => [
                    'type' => 'string',
                    'title' => 'Slug del servizio',
                    'default' => 'segnalazione-disservizio',
                    'required' => true,
                ],
                'severity_1' => [
                    'type' => 'string',
                    'title' => 'Mappatura per valutazione importanza 1',
                    'enum' => array_column($this->types, 'identifier'),
                    'default' => 'suggerimento',
                    'required' => true,
                ],
                'severity_2' => [
                    'type' => 'string',
                    'title' => 'Mappatura per valutazione importanza 2',
                    'enum' => array_column($this->types, 'identifier'),
                    'default' => 'suggerimento',
                    'required' => true,
                ],
                'severity_3' => [
                    'type' => 'string',
                    'title' => 'Mappatura per valutazione importanza 3',
                    'enum' => array_column($this->types, 'identifier'),
                    'default' => 'segnalazione',
                    'required' => true,
                ],
                'severity_4' => [
                    'type' => 'string',
                    'title' => 'Mappatura per valutazione importanza 4',
                    'enum' => array_column($this->types, 'identifier'),
                    'default' => 'segnalazione',
                    'required' => true,
                ],
                'severity_5' => [
                    'type' => 'string',
                    'title' => 'Mappatura per valutazione importanza 5',
                    'enum' => array_column($this->types, 'identifier'),
                    'default' => 'reclamo',
                    'required' => true,
                ],
            ],
            'dependencies' => [
                'base_url' => ['is_enabled'],
                'api_login' => ['is_enabled'],
                'api_password' => ['is_enabled'],
                'default_group_name' => ['is_enabled'],
                'service_identifier' => ['is_enabled'],
                'service_slug' => ['is_enabled'],
                'severity_1' => ['is_enabled'],
                'severity_2' => ['is_enabled'],
                'severity_3' => ['is_enabled'],
                'severity_4' => ['is_enabled'],
                'severity_5' => ['is_enabled'],
            ]
        ];
    }

    protected function getOptions()
    {
        $severity = [
            'type' => 'select',
            'hideNone' => true,
            'optionLabels' => array_column($this->types, 'name'),
            'dependencies' => ['is_enabled' => true],
        ];
        $options = [
            'form' => [
                'attributes' => [
                    'action' => $this->getHelper()->getServiceUrl('action', $this->getHelper()->getParameters()),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ],
            'fields' => [
                'is_enabled' => [
                    'type' => 'checkbox',
                    'rightLabel' => 'Abilita il connettore con le Segnalazioni disservizio di Area personale',
                ],
                'base_url' => [
                    'placeholder' => 'https://servizi.comune.bugliano.pi.it/lang',
                    'helper' => 'Comprensivo del suffisso (esempio: https://servizi.comune.bugliano.pi.it/lang)',
                    'dependencies' => ['is_enabled' => true],
                ],
                'api_login' => ['dependencies' => ['is_enabled' => true],],
                'api_password' => ['dependencies' => ['is_enabled' => true],],
                'default_group_name' => ['dependencies' => ['is_enabled' => true],],
                'service_identifier' => ['dependencies' => ['is_enabled' => true],],
                'service_slug' => ['dependencies' => ['is_enabled' => true],],
                'severity_1' => $severity,
                'severity_2' => $severity,
                'severity_3' => $severity,
                'severity_4' => $severity,
                'severity_5' => $severity,
            ],
        ];

        return $options;
    }

    protected function getView()
    {
        $parent = 'bootstrap-edit';

        return [
            'parent' => $parent,
            'locale' => $this->getAlpacaLocale(),
        ];
    }

    protected function submit()
    {
        $data = $_POST;

        foreach ($data as $key => $value) {
            if (strpos($key, 'severity_') !== false) {
                $key = str_replace('severity_', '', $key);
                $this->inefficiencySettings->severity_map[$key] = $value;
            } elseif ($key === 'api_password' && $value === 'EZ_PASSWORD') {
                continue;
            } elseif ($key === 'base_url') {
                $url = parse_url($value);
                if (!isset($url['scheme'], $url['host'], $url['path'])) {
                    throw new InvalidArgumentException('Invalid url');
                }
                $this->inefficiencySettings->{$key} = $value;
            } elseif (isset($this->inefficiencySettings->{$key})) {
                $this->inefficiencySettings->{$key} = $value;
            }
        }

        $this->inefficiencySettings->is_enabled = $this->inefficiencySettings->is_enabled === 'true';

        if ($this->inefficiencySettings->is_enabled) {
            $inefficiencyClient = (new Client\HttpClient($this->inefficiencySettings->base_url))
                ->addCredential(
                    Client\Credential::API_USER,
                    $this->inefficiencySettings->api_login,
                    $this->inefficiencySettings->api_password
                );
            $inefficiencyClient->getCredential(Client\Credential::API_USER, true);
            OpenPaSensorRepository::instance()->setInefficiencySettings($this->inefficiencySettings);
        }else{
            OpenPaSensorRepository::instance()->setInefficiencySettings(null);
        }

        return true;
    }

    protected function upload()
    {
        throw new RuntimeException('Not enabled');
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
}