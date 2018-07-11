<?php

declare(strict_types=1);

$container = require __DIR__ . '/../app/bootstrap.php';

$container->getService('application')->run();
