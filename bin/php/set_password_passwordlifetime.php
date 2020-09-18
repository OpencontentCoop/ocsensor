<?php
require 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' => ( "Set password lifetime" ),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions('[days:][dry-run]',
    '',
    array(
        'days' => 'Lifetime days',
        'dry-run' => 'Drai ran'
    )
);
$script->initialize();
$script->setUseDebugAccumulators( true );

$db = eZDB::instance();

try
{
    $db = eZDB::instance();
    $db->setErrorHandling( eZDB::ERROR_HANDLING_EXCEPTIONS );

    $days = (int)$options['days'];
    if ($days == 0){
        throw new Exception("Il valore di 'days' deve essere maggiore di zero");
    }

    $list = $db->arrayQuery( "SELECT * FROM ezx_mbpaex where passwordlifetime > 0;" );
    $count = count($list);
    $cli->output( "Ci sono $count password lifetime" );

    /** @var eZPaEx[] $eZPaExList */
    $eZPaExList = eZPersistentObject::handleRows( $list, 'eZPaEx', true );

    foreach ($eZPaExList as $item){
        $current = $item->attribute('passwordlifetime' );
        $passwordLastUpdated = $item->attribute( 'password_last_updated' );
        $actualPasswordlifetime = ceil( ( ( ( ( time() - $passwordLastUpdated ) / 60 ) / 60 ) / 24 ) );
        $isExpired = intval($actualPasswordlifetime > $days);
        $userId = $item->attribute('contentobject_id');
        $cli->output(" - Modifico valore da $current a $days per l'utente #$userId ($isExpired)");
        if (!$options['dry-run']) {
            $item->setAttribute('passwordlifetime', $days);
            $item->resetPasswordLastUpdated();
        }
    }

    $script->shutdown();
}
catch( eZDBException $e )
{
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, 'Tabella ezx_mbpaex non installata' );
}
catch( Exception $e )
{
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}

$script->shutdown();