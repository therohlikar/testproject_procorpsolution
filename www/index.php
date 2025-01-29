<?php

declare(strict_types=1);

use Apitte\Core\Application\Application as ApitteApplication;
use App\Bootstrap;

require __DIR__ . '/../vendor/autoload.php';

$bootstrap = new App\Bootstrap;
$container = $bootstrap->bootWebApplication();
$application = $container->getByType(ApitteApplication::class);
$application->run();
