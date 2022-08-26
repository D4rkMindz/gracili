<?php

use App\Service\SettingsInterface;
use Cake\Database\Connection;
use Slim\App;

putenv('APP_CONFIG=phinx');
// I know, but this is for the resize image migration
global $app;
/** @var App $app */
$app = require __DIR__ . '/bootstrap.php';

$container = $app->getContainer();
$pdo = $container->get(Connection::class)->getDriver()->getConnection();
$pdoTest = $container->get(Connection::class . '_test')->getDriver()->getConnection();

return [
    'paths' => [
        'migrations' => $container->get(SettingsInterface::class)->get('migrations'),
        'seeds' => $container->get(SettingsInterface::class)->get('seeds'),
    ],
    'environments' => [
        'default_database' => 'local',
        'local' => [
            'name' => $container->get(SettingsInterface::class)->get('db')['database'],
            'connection' => $pdo,
        ],
        'test' => [
            'name' => $container->get(SettingsInterface::class)->get('db')['testing'],
            'connection' => $pdoTest,
        ],
    ],
];

