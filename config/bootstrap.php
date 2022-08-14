<?php

use App\Service\SettingsInterface;
use DI\ContainerBuilder;
use Slim\App;
use Symfony\Component\Translation\Translator;

if (file_exists(__DIR__ . '/environment.php')) {
    require_once __DIR__ . '/environment.php';
}
if (file_exists(__DIR__ . '/../../environment.php')) {
    require_once __DIR__ . '/../../environment.php';
}
if (!defined('APP_ENV')) {
    throw new RuntimeException('Application Environment not configured');
}

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/container/bootstrap.php');
$container = $containerBuilder->build();

/** @var App $app */
$app = $container->get(App::class);

// Set up dependencies
//require __DIR__ . '/../config/container/bootstrap.php';

// Register routes
$routeBuilder = require __DIR__ . '/../config/routes.php';
$routeBuilder($app);

// Register middlewares
$middlewareBuilder = require __DIR__ . '/../config/middleware.php';
$middlewareBuilder($app);

// set App
__($app->getContainer()->get(Translator::class));

$settings = $app->getContainer()->get(SettingsInterface::class);

return $app;