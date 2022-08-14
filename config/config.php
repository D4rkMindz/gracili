<?php

$defaults = require __DIR__ . '/defaults.php';

$env = [];

if (file_exists(__DIR__ . '/../../env.php')) {
    $env = require __DIR__ . '/../../env.php';
} else {
    if (file_exists(__DIR__ . '/env.php')) {
        $env = require __DIR__ . '/env.php';
    } else {
        throw new InvalidArgumentException('env.php not found');
    }
}

if (defined('APP_ENV') && !empty(APP_ENV) && is_file(__DIR__ . '/env.' . APP_ENV . '.php')) {
    $config = require __DIR__ . '/env.' . APP_ENV . '.php';
    $defaults = array_replace_recursive($defaults, $config);
}

// APP_CONFIG because of symfonys APP_ENV
$appEnv = getenv('APP_CONFIG') ?: null;
if (!empty($appEnv) && is_file(__DIR__ . '/env.' . $appEnv . '.php')) {
    $config = require __DIR__ . '/env.' . $appEnv . '.php';
    $defaults = array_replace_recursive($defaults, $config);
}

$defaults = array_replace_recursive($defaults, $env);

return $defaults;
