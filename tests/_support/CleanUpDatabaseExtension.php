<?php

use Codeception\Event\TestEvent;
use Codeception\Events;

class CleanUpDatabaseExtension extends \Codeception\Extension
{
    public static array $events = [
        Events::TEST_BEFORE => 'before',
    ];

    /**
     * Clean up the database before every test
     *
     * @param TestEvent $event
     *
     * @return void
     */
    public function before(TestEvent $event)
    {
        $php = exec('which php');
        $populate = __DIR__ . '/../../bin/populate.php';
        exec($php . ' ' . $populate);
    }
}