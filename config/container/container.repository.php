<?php

use App\Repository\UserRepository;
use Slim\Container;

$container = $app->getContainer();

/**
 * Get UserRepository.
 *
 * @param Container $container
 * @return UserRepository
 */
$container[UserRepository::class] = function (Container $container) {
    return new UserRepository($container);
};
