#!/bin/php
<?php

$start = microtime(true);

$logfile = __DIR__ . '/../tmp/' . date('Ymd_His') . '_release.log';
require_once __DIR__ . '/functions.php';

$log = ['Starting deployment at ' . date('Y-m-d H:i:s')];
$secretsFile = __DIR__ . '/../secrets.json';
$filesToRemove = [
    'bin/deploy.php',
    'bin/generate-migration.php',
    'bin/increase-version.php',
    'bin/enqueue/test.php',
    'config/env.example.php',
    '.cs.php',
    '.editorconfig',
    '.gitignore',
    '.travis.yml',
    'codestyle.xml',
    'phpstan.neon',
    'README.md',
    'secrets.example.json',
    'secrets.json',
];
$directoriesToRemove = [
    '.git',
    'tests',
    'data',
];

if (!file_exists($secretsFile)) {
    die('secrets.json in project root not found');
}

$buildDir = __DIR__ . '/../dist';
$options = getopt('b:e:d:');
$branch = $options['b'] ?? 'master';
$environment = $options['e'] ?? 'prod';
$secrets = json_decode(file_get_contents(__DIR__ . '/../secrets.json'), true);
$gitRepository = $secrets['repo'];// 'prod' or 'beta'
$deploymentConfig = $secrets[$environment];// 'prod' or 'beta'
if (empty($deploymentConfig)) {
    shout('Environment not found');
    exit(255);
}

rrmdir($buildDir);
mkdir($buildDir);

shout("downloading latest version ...");
$log += run("git clone --single-branch --branch {$branch} {$gitRepository} {$buildDir}");

shout("removing non productive files");
foreach ($filesToRemove as $file) {
    $f = $buildDir . '/' . $file;
    shout("removing {$f}", true);
    if (is_file($f)) {
        unlink($f);
    } else {
        shout("{$f} not removed, doesn't exist)", true);
    }
    shout("", true, false);
}

shout("removing non productive directories");
foreach ($directoriesToRemove as $directory) {
    $d = $buildDir . '/' . $directory;
    shout("removing {$d}", true);
    if (is_dir($d)) {
        rrmdir($d);
    } else {
        shout(' (not removed, doesn\'exist)', true);
    }
}

shout("installing dependencies ...");
$log += run("cd {$buildDir} && composer install --no-ansi --no-dev --no-interaction --no-progress --no-scripts --optimize-autoloader");
shout("installing JS dependencies ...");
$log += run("cd {$buildDir} && npm install");
shout("compiling assets ...");
$log += run("cd {$buildDir} && npm run build");
rrmdir($buildDir . '/node_modules');


rrmdir($buildDir . '/bin/__MACOSX'); // bc mac...

$date = date('Ymd_His');
// parse_twig($buildDir);
// copy_secretsJSON($buildDir);

$log += run("cd {$buildDir}");

shout("creating remote directory ...");
$log += run("ssh {$deploymentConfig['host']} 'rm -rf {$deploymentConfig['root']}/release'");
$log += run("ssh {$deploymentConfig['host']} 'mkdir -p {$deploymentConfig['root']}/release {$deploymentConfig['root']}/application_{$date}'");

shout("zipping files ...");
$zipName = "application_{$date}.zip";
$zip = "{$buildDir}/{$zipName}";
$log += run("cd {$buildDir} && zip -r {$zip} .");

shout("uploading zip ...");
$log += run("scp {$zip} {$deploymentConfig['host']}:{$deploymentConfig['root']}/release/");

shout("unzipping zip ...");
$log += run("ssh {$deploymentConfig['host']} 'unzip -u {$deploymentConfig['root']}/release/{$zipName} -d {$deploymentConfig['root']}/release'");
$log += run("ssh {$deploymentConfig['host']} 'mv {$deploymentConfig['root']}/release/{$zipName} {$deploymentConfig['root']}/backups/{$zipName}'");

shout("stopping enqueue gracefully (restarting automatically after deployment)");
$log += run("ssh {$deploymentConfig['host']} 'find {$deploymentConfig['root']}/release/bin/ -type f -exec chmod 775 -- {} +'");
$log += run("ssh {$deploymentConfig['host']} 'sh {$deploymentConfig['root']}/release/bin/enqueue/stop.sh'");

shout("executing migrations ...");
$log += ["----------------------- Migrations -----------------------\n"];
$log += run("ssh {$deploymentConfig['host']} 'php {$deploymentConfig['root']}/release/bin/migrate.php'");
$log += ["----------------------------------------------------------\n"];

shout("moving application ...");
// first move and then remove is quicker than removing it immediately
$log += run("ssh {$deploymentConfig['host']} 'mv {$deploymentConfig['root']}/application {$deploymentConfig['root']}/application_{$date}'");
$log += run("ssh {$deploymentConfig['host']} 'mv {$deploymentConfig['root']}/release {$deploymentConfig['root']}/application'");
$log += run("ssh {$deploymentConfig['host']} 'rm -rf {$deploymentConfig['root']}/application_{$date}'");

$time = round(microtime(true) - $start, 4);
shout("All done. Took {$time} seconds");
shout("Logs available in \nfile://" . $logfile);
