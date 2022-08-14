<?php

$container = [];
require __DIR__ . '/container.php';

// load environment specific container when defined
if (defined('APP_ENV') && !empty(APP_ENV) && is_file(__DIR__ . '/container.' . strtolower(APP_ENV) . '.php')) {
    require __DIR__ . '/container.' . strtolower(APP_ENV) . '.php';
}
// also allow loading of a container based on an environment variable
$appEnv = getenv('APP_CONFIG') ?: null;
if (!empty($appEnv) && is_file(__DIR__ . '/container.' . strtolower($appEnv) . '.php')) {
    require __DIR__ . '/container.' . strtolower($appEnv) . '.php';
}

$configFile = __DIR__ . '/../../.APP_CONFIG';
if (file_exists($configFile)) {
    $appConfig = file_get_contents($configFile);
    if (!empty($appConfig) && is_file(__DIR__ . '/container.' . strtolower($appConfig) . '.php')) {
        require __DIR__ . '/container.' . strtolower($appConfig) . '.php';
    }
}

return $container;
