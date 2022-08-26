<?php
// global test bootstrap file
// this is configured in the codeception.yml configuration

require __DIR__ . '/_support/EnvironmentExtension.php'; // Codeception will not find this extension if not manually required.
require __DIR__ . '/_support/CleanUpDatabaseExtension.php'; // Codeception will not find this extension if not manually required.

// Run migrations (if any)
$root = __DIR__ . '/..';
$command = 'php ' . $root . '/bin/migrate.php -e test';

system($command);
