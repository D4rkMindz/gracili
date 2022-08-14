<?php

use Crunz\Schedule;

$scheduler = new Schedule();
$task = $scheduler->run('sh start.sh');
$task
    ->description('Start one enqueue runner')
    ->in(__DIR__ . '/../../bin/enqueue')
    ->everyFifteenMinutes();

return $scheduler;
