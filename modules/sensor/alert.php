<?php

$tpl = eZTemplate::factory();
$repository = OpenPaSensorRepository::instance();
$alerts = $repository->getUserService()->getAlerts($repository->getCurrentUser());
$tpl->setVariable( 'has_alerts', count( $alerts ) > 0 );
$tpl->setVariable( 'alerts', $alerts );
echo $tpl->fetch( 'design:social_user/alerts.tpl' );
eZExecution::cleanExit();
