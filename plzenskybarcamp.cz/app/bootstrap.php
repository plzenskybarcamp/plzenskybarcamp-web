<?php

use Nette\Application\Routers\Route;

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

//$configurator->setDebugMode(TRUE);  // debug mode MUST NOT be enabled on production server
$configurator->enableDebugger(__DIR__ . '/../log');

$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->addDirectory(__DIR__ . '/../vendor/others')
	->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
//$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();

Route::$defaultFlags |= ($container->httpRequest->isSecured() ? Route::SECURED : 0);

return $container;
