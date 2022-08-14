#!/bin/php
<?php
$options = getopt('e:');
$environment = $options['e'] ?? false;

$command = 'cd ' . __DIR__ . '/../config && ' . __DIR__ . '/../vendor/robmorgan/phinx/bin/phinx migrate -c ' . __DIR__ . '/../config/phinx.php';
if ($environment) {
    $command .= ' -e ' . $environment;
}
echo "Migrating database using:\n$ {$command}\n";
system($command);
echo "Done migrating\n-----------------------------------------------------------------------------\n";
