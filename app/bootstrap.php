<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

$configurator->setDebugMode([]); // enable for your remote IP
$configurator->enableDebugger(__DIR__ . '/../log', 'pan@jakubboucek.cz');

$configurator->setTempDirectory(__DIR__ . '/../temp');

if (!file_exists($sessDir = __DIR__ . '/../temp/sessions') && !mkdir($sessDir) && !is_dir($sessDir)) {
    throw new \RuntimeException(sprintf('Directory "%s" was not created', $sessDir));
}

$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
if (file_exists(__DIR__ . '/config/config.local.neon')) {
    $configurator->addConfig(__DIR__ . '/config/config.local.neon');
}

return $configurator->createContainer();
