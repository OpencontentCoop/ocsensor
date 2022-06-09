<?php

require 'autoload.php';

$script = eZScript::instance(array(
        'description' => ("Import custom translations from csv\n\n"),
        'use-session' => false,
        'use-modules' => true,
        'use-extensions' => true)
);

$script->startup();

$options = $script->getOptions(
    '[from-json:][url:]',
    '',
    array(
        'from-json' => 'Path of json file',
        'url' => 'Google Spreadsheet url',
    )
);
$script->initialize();
$script->setUseDebugAccumulators(true);
$cli = eZCLI::instance();
$output = new ezcConsoleOutput();

if (empty($options['from-json'])) {
    if (empty($options['url'])) {
        $opts = new ezcConsoleQuestionDialogOptions();
        $opts->text = "Inserisci l'url del Google Spreadsheet";
        $opts->showResults = true;
        $question = new ezcConsoleQuestionDialog($output, $opts);
        $googleSpreadsheetUrl = ezcConsoleDialogViewer::displayDialog($question);
    } else {
        $googleSpreadsheetUrl = $options['url'];
    }
    $googleSpreadsheetTemp = explode(
        '/',
        str_replace('https://docs.google.com/spreadsheets/d/', '', $googleSpreadsheetUrl)
    );
    $googleSpreadsheetId = array_shift($googleSpreadsheetTemp);

    $sheet = new \Opencontent\Google\GoogleSheet($googleSpreadsheetId);
    $feedTitle = (string)$sheet->getTitle();
    $sheets = $sheet->getSheetTitleList();

    $menu = new ezcConsoleMenuDialog($output);
    $menu->options = new ezcConsoleMenuDialogOptions();
    $menu->options->text = "Seleziona il foglio:\n";
    $menu->options->validator = new ezcConsoleMenuDialogDefaultValidator($sheets);
    $choice = ezcConsoleDialogViewer::displayDialog($menu);

    $sheetChoice = $sheets[$choice];
    $csv = $sheet->getSheetDataHash($sheetChoice);
    $data = [];
    foreach ($csv as $row) {
        $source = $row['source'];
        unset($row['context']);
        unset($row['source']);
        $row['eng-GB'] = $row['eng-US'];
        unset($row['eng-US']);
        $data[$source] = $row;
    }
    file_put_contents('/var/www/html/custom-translations.json', json_encode($data));
}else{
    $data = json_decode(file_get_contents($options['from-json']), true);
}

$translationsHelper = SensorTranslationHelper::instance();
$dataCount = count($data);
$cli->warning("Importo $dataCount elementi");
foreach($data As $key => $languages){
    $cli->output(' - ' . $key);
    $translationsHelper->addCustomTranslation($key, $languages);
}

$translationsHelper->resetStaticTranslations();

$script->shutdown();