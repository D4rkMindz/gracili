<?php

use App\Service\SettingsInterface;
use App\Util\SimpleLogger;
use League\Flysystem\FilesystemInterface;
use Psr\Log\LoggerInterface;

/**
 * See simple logger description in container.php !!
 *
 * @param FilesystemInterface $filesystem
 * @param SettingsInterface   $settings
 *
 * @return SimpleLogger
 */
$container[SimpleLogger::class] = static function (FilesystemInterface $filesystem, SettingsInterface $settings) {
    // make sure that the file system is called and set up.
    // https://stackoverflow.com/questions/24271489/configure-php-monolog-to-log-to-amazon-s3-via-stream
    // https://stackoverflow.com/a/24272614/6805097
    // formerly it was $container->get(FileSystemInterface::class). This is to remove the container dependency
    $filesystem->has('/');

    $name = $settings->get('name');
    $path = $settings->get(LoggerInterface::class)['enqueue'];

    return SimpleLogger::factory($name, $path);
};