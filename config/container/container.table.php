<?php

use App\Table\UserTable;
use Cake\Database\Connection;

$container = $app->getContainer();

$connection = $container->get(Connection::class);

/**
 * Get UserTable.
 *
 * @return UserTable
 */
$container[UserTable::class] = function () use ($connection){
    return new UserTable($connection);
};
