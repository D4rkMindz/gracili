<?php
// load parameters that can be used like e.g. %APP_URL% in any configuration .yml file
// this is configured in the codeception.yml configuration

$config = require __DIR__ . '/../config/config.php';

return [
    'DB_HOST' => $config['db']['host'],
    'DB_PORT' => $config['db']['port'],
    'DB_NAME' => $config['db']['testing'],
    'DB_USERNAME' => $config['db']['username'],
    'DB_PASSWORD' => $config['db']['password'],
    'APP_URL' => $config['baseUrl'],
];
