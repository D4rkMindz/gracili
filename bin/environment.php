#!/bin/php
<?php

$logfile = __DIR__ . '/../../tmp/enqueue/' . date('Ymd_His') . '.log';
require __DIR__ . '/functions.php';

if (defined('APP_ENV')) {
    echo APP_ENV;
} else {
    echo "NO_ENVIRONMENT";
}