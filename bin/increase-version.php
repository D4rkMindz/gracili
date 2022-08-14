#!/bin/php
<?php

use CzProject\GitPhp\Git;

$versionFile = __DIR__ . '/../public/version';
$start = microtime(true);

$logfile = __DIR__ . '/../tmp/' . date('Ymd_His') . '_release.log';
require_once __DIR__ . '/functions.php';

$options = getopt('v:n:');
$version = $options['v'] ?? false;
$notes = $options['n'] ?? false;

$projectBasePath = __DIR__ . '/your-app/';
if (empty($notes) || !file_exists($notes) || !file_exists($projectBasePath . $notes)) {
    shout('Please provide a valid version notes file by entering its path using -n');
    exit();
}
if (file_exists($notes)) {
    $versionNotes = file_get_contents($notes);
} else {
    if (file_exists($projectBasePath . $notes)) {
        $versionNotes = file_get_contents($projectBasePath . $notes);
    }
}

if (empty($versionNotes)) {
    shout('Empty version notes provided. Please fill out your file ' . $notes);
    exit ();
}

if (empty($version)) {
    $version = ask('What version do you want to publish?' . PHP_EOL);
}

$git = new Git();
$repo = $git->open($projectBasePath);


$branch = $repo->getCurrentBranchName();
if ($branch !== 'develop') {
    shout("You can only set a version within the develop branch!");
    exit();
}

$tags = $repo->getTags();

if (in_array($version, $tags)) {
    shout('Version already exists');
    exit();
}

sort($tags);

$latestVersion = end($tags);
if (version_compare($latestVersion, $version, '>=')) {
    shout('You can only increase versions. Latest tag is ' . $latestVersion);
    exit();
}

if ($repo->hasChanges()) {
    shout('Please commit your changes before increasing your version');
    exit();
}

$previousMajor = (int)explode('.', $latestVersion, 2)[0];
$currentMajor = (int)explode('.', $version, 2)[0];
$isMajorIncrease = $currentMajor > $previousMajor;
if ($isMajorIncrease) {
    shout('Building major version notes');
    // confirm('You are increasing the major version. Version notes are compiled by using all previous tags infos. Are you sure?', true);
    $tagTexts = $repo->execute('tag', '-l', '-n10', $previousMajor . '.*');
    $finalNotes = [];
    $lastVersion = '-';
    foreach ($tagTexts as $key => $tagText) {
        if (str_contains($tagText, '🔥') && str_contains(strtolower($tagText), 'version')) {
            $lastVersion = trim(explode('🔥', $tagText)[0]);
            $finalNotes[$lastVersion] = [];
        }
        if (str_starts_with(trim($tagText), '-')) {
            $finalNotes[$lastVersion][] = trim($tagText);
        }
    }
    uksort($finalNotes, "version_compare");
    $versionMessage = '📦🔥 Publish version ' . $version . PHP_EOL . PHP_EOL;
    $versionMessage .= $versionNotes . PHP_EOL . PHP_EOL;
    $versionMessage .= '*Previous versions*' . PHP_EOL;
    if (isset($finalNotes[$previousMajor . '.0.0'])) {
        unset($finalNotes[$previousMajor . '.0.0']);
    }
    foreach ($finalNotes as $v => $versionNotes) {
        $versionMessage .= $v . PHP_EOL;
        $versionMessage .= implode(PHP_EOL, $versionNotes);
        $versionMessage .= PHP_EOL . PHP_EOL;
    }
} else {
    shout('You are about to publish version ' . $version . ' with following notes: ' . PHP_EOL . $versionNotes);
    confirm('Do you want to continue?', true);
    $versionMessage = '🔥 Publish version ' . $version . PHP_EOL . PHP_EOL . $versionNotes;
}
shout('Updating repository');
$repo->fetch('origin');
$repo->pull();

shout('Writing version');
file_put_contents($versionFile, '');
file_put_contents($versionFile, $version);

shout('Checking out master branch');
$repo->checkout('master');
shout('Merging develop into master');
$repo->merge('develop');

if ($repo->hasChanges()) {
    $repo->addAllChanges();
    $repo->commit('🔥 Publish version ' . $version);
}
shout('Creating tag ' . $version);
$repo->createTag($version, [
    '-m' => $versionMessage,
]);
$repo->push();
$repo->push('origin', ['--tags']);
shout('Checking out develop branch');
$repo->checkout('develop');
shout('Merging master into develop');
$repo->merge('master');
$repo->push();
shout('done');
