<?php

require 'autoload.php';

$script = eZScript::instance(array(
        'description' => ("Send fake event\n\n"),
        'use-session' => false,
        'use-modules' => true,
        'use-extensions' => true)
);

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators(true);

$user = eZUser::fetchByName('admin');
eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));

$repository = OpenPaSensorRepository::instance();

class TestSensorSocketEmitterListener extends SensorSocketEmitterListener
{
    public function sendFake()
    {
        $this->repository->getLogger()->info('Send fake data to socket');
        $this->send([
            'identifier' => 'on_create',
            'data' => [
                'id' => 0,
                'creator' => 0,
                'users' => [],
                'groups' => [],
            ]
        ]);
    }
}

(new TestSensorSocketEmitterListener(
    $repository,
    $repository->getSensorSettings()->get('SocketSecret'),
    $repository->getSensorSettings()->get('SocketInternalUrl'),
    $repository->getSensorSettings()->get('SocketPort')
))->sendFake();


$script->shutdown();