#!/bin/php
<?php

use App\Queue\Extension\SignalExtension;
use App\Queue\Extension\SignalHandler\AbortHandler;
use Enqueue\Consumption\ChainExtension;
use Enqueue\SimpleClient\SimpleClient;
use Psr\Log\LoggerInterface;

putenv('APP_CONFIG=enqueue');

$logfile = __DIR__ . '/../../tmp/enqueue/' . date('Ymd_His') . '.log';
require __DIR__ . '/../functions.php';
/** @var LoggerInterface $logger */
$logger = container(LoggerInterface::class);
/** @var SimpleClient $client */
$client = container(SimpleClient::class);
/** @var AbortHandler $abortHandler */
$abortHandler = container(AbortHandler::class);
$logger->debug('Starting enqueue');

$signalListeners = [
    $abortHandler->toListener(),
];

$client->consume(new ChainExtension([
    new SignalExtension($signalListeners),
]));