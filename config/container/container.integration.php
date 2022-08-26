<?php

use App\Service\SettingsInterface;
use Cake\Database\Connection;
use Cake\Database\Driver\Mysql;

/**
 * The database connection for testing
 *
 * @param SettingsInterface $settings
 *
 * @return Connection
 */
$container[Connection::class] = static function (SettingsInterface $settings) {
    $config = $settings->get('db');
    $driver = new Mysql([
        'host' => $config['host'],
        'port' => $config['port'],
        'database' => $config['testing'],
        'username' => $config['migration']['username'],
        'password' => $config['migration']['password'],
        'encoding' => $config['encoding'],
        'charset' => $config['charset'],
        'collation' => $config['collation'],
        'prefix' => '',
        'flags' => [
            // Enable exceptions
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Set default fetch mode
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ],
    ]);
    $driver->enableAutoQuoting(true);
    $db = new Connection([
        'driver' => $driver,
    ]);

    $db->connect();

    return $db;
};