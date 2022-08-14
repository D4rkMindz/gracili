<?php

namespace App\Util;

use Crunz\Application\Service\ConfigurationInterface;
use Crunz\Application\Service\LoggerFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\App;

class CrunzLoggerFactory implements LoggerFactoryInterface
{
    public function create(ConfigurationInterface $configuration): LoggerInterface
    {
        /** @var App $app */
        $app = require __DIR__ . '/../../config/bootstrap.php';

        return $app->getContainer()->get(LoggerInterface::class);
    }
}