<?php

use App\Queue\Image\ResizeImageProcessor;
use Enqueue\SimpleClient\SimpleClient;
use Interop\Queue\ConnectionFactory;

$logfile = __DIR__ . '/../../tmp/enqueue/' . date('Ymd_His') . '.log';
require __DIR__ . '/../functions.php';
/** @var ConnectionFactory $factory */
$factory = container(ConnectionFactory::class);
/** @var SimpleClient $client */
$client = container(SimpleClient::class);
$client->sendCommand(ResizeImageProcessor::class, "my data");