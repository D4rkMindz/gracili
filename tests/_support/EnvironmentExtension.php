<?php

use Codeception\Event\SuiteEvent;
use Codeception\Events;

/**
 * Extension to run specific code before every suite
 *
 * This file must be required manually (in tests/_bootstrap.php) and then be added as extension in the global tests
 * configuration file
 */
class EnvironmentExtension extends \Codeception\Extension
{
    public static $events = [
        Events::SUITE_BEFORE => 'before',
        Events::SUITE_AFTER => 'after',
    ];

    private string $file;

    public function before(SuiteEvent $event)
    {
        // Set the app config file since there is NO WAY to change the environment for testing
        // Constants and Environment variables are NOT passed to the application during testing since it simulates a real request
        $this->file = __DIR__ . '/../../.APP_CONFIG';
        file_put_contents($this->file, 'integration');
    }

    public function after(SuiteEvent $event)
    {
        // clean up after the suite (so dev wont be impacted)
        // however, if you use the application while testing, you will see testing data
        unlink($this->file);
    }
}