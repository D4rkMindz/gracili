<?php

if (!isset($GLOBALS['logfile'])) {
    echo "ERR: NO_LOG_FILE" . PHP_EOL;
    die(100);
}

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Moment\Moment;

require_once __DIR__ . '/../vendor/autoload.php';

// I know
global $APP;
$APP = require __DIR__ . '/../config/bootstrap.php';

/**
 * Receive anything from the container
 *
 * @param string $identifier
 *
 * @return mixed
 */
function container(string $identifier)
{
    return $GLOBALS['APP']->getContainer()->get($identifier);
}

/**
 * Run a command
 *
 * @param string $command
 *
 * @return array
 */
function run(string $command): array
{
    $output = [];
    exec($command . '  2>&1', $output);
    $out = implode("\n", $output);
    file_put_contents($GLOBALS['logfile'], $out, FILE_APPEND);

    return $output;
}

/**
 * Ask the user something and receive the input
 *
 * @param string $question
 *
 * @return string
 */
function ask(string $question): string
{
    $answer = (string)readline($question . " ");
    $log = $question . ' ' . $answer;
    file_put_contents($GLOBALS['logfile'], $log, FILE_APPEND);

    return $answer;
}

/**
 * Force the user to confirm a question (y/n)
 *
 * @param string $question
 * @param bool   $cancelExecutionOnNo
 *
 * @return bool
 */
function confirm(string $question, bool $cancelExecutionOnNo): bool
{
    $answer = '';
    $allowed = ['y', 'n'];
    while (!in_array(strtolower($answer), $allowed)) {
        $answer = ask($question . ' [y/n]');
    }
    $confirmed = strtolower($answer) === 'y';
    if ($cancelExecutionOnNo && !$confirmed) {
        shout('Aborted');
        exit();
    }

    return $confirmed;
}

/**
 * Use this instead of echo
 *
 * @param string $message
 * @param bool   $silent
 * @param bool   $includeTime
 */
function shout(string $message, ?bool $silent = false, ?bool $includeTime = true)
{
    $log = $message;
    $date = new Moment();
    if ($includeTime === true) {
        $log = "[{$date->format('H:i:s')}] " . $message . PHP_EOL;
    }

    file_put_contents($GLOBALS['logfile'], $log, FILE_APPEND);

    if ($silent === false) {
        $time = '';
        if ($includeTime) {
            $time = "\e[32m{$date->format('H:i:s')}\e[0m ";
        }
        echo $time . $message . PHP_EOL;
    }
}

/**
 * Download a file
 *
 * @param string $url
 * @param string $to
 */
function download(string $url, string $to)
{
    file_put_contents($to, file_get_contents($url));
}

/**
 * Get a local filesystem
 *
 * @param string $root The Root of the local filesystem to use
 *
 * @return Filesystem
 */
function local(string $root): Filesystem
{
    return new Filesystem(new Local($root));
}

/**
 * Get all defined classes in a directory
 *
 * @see https://programmierfrage.com/items/symfony-2-find-all-classes-in-a-namespace
 *
 * @param string $path
 *
 * @return array
 */
function classesInDirectory(string $path): array
{
    $fqcns = [];

    $allFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
    $phpFiles = new RegexIterator($allFiles, '/\.php$/');
    foreach ($phpFiles as $phpFile) {
        $content = file_get_contents($phpFile->getRealPath());
        $tokens = token_get_all($content);
        $namespace = '';
        for ($index = 0; isset($tokens[$index]); $index++) {
            if (!isset($tokens[$index][0])) {
                continue;
            }
            if (
                T_NAMESPACE === $tokens[$index][0]
                && T_WHITESPACE === $tokens[$index + 1][0]
                && T_STRING === $tokens[$index + 2][0]
            ) {
                $namespace = $tokens[$index + 2][1];
                // Skip "namespace" keyword, whitespaces, and actual namespace
                $index += 2;
            }
            if (
                T_CLASS === $tokens[$index][0]
                && T_WHITESPACE === $tokens[$index + 1][0]
                && T_STRING === $tokens[$index + 2][0]
            ) {
                $fqcns[] = $namespace . '\\' . $tokens[$index + 2][1];
                // Skip "class" keyword, whitespaces, and actual classname
                $index += 2;

                # break if you have one class per file (psr-4 compliant)
                # otherwise you'll need to handle class constants (Foo::class)
                break;
            }
        }
    }

    return $fqcns;
}
