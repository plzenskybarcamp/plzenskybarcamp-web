<?php

use Nette\Application\Routers\Route;

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

//$configurator->setDebugMode(TRUE);  // debug mode MUST NOT be enabled on production server
$configurator->enableDebugger(__DIR__ . '/../log', 'pan@jakubboucek.cz');

$configurator->setTempDirectory(__DIR__ . '/../temp');
if(!file_exists(__DIR__ . '/../temp/sessions')) mkdir(__DIR__ . '/../temp/sessions');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->addDirectory(__DIR__ . '/../vendor/others')
	->addDirectory(__DIR__ . '/../vendor/facebook')
	->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();

Route::$defaultFlags |= ($container->httpRequest->isSecured() ? Route::SECURED : 0);

return $container;
