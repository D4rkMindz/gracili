<?php

namespace App\Util;

use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Processor\WebProcessor;
use Psr\Log\LoggerInterface;

class SimpleLogger extends Logger implements LoggerInterface
{
    /**
     * Simple loggerr without mail
     *
     * @param string $name
     * @param string $path
     *
     * @return SimpleLogger
     */
    public static function factory(string $name, string $path)
    {
        $logger = new self($name);

        $console = new StreamHandler('php://stdout');
        $output = "[%datetime%] %channel%.%level_name%: %message%\n";
        $consoleFormatter = new LineFormatter($output);
        $console->setFormatter($consoleFormatter);
        $logger->pushHandler($console);

        $log = new StreamHandler($path . '.log');
        $log->setFormatter(new LineFormatter());
        $logger->pushHandler($log);

        $html = new StreamHandler($path . '.html');
        $html->setFormatter(new HtmlFormatter());
        $logger->pushHandler($html);

        $json = new StreamHandler($path . '.json');
        $json->setFormatter(new JsonFormatter());
        $logger->pushHandler($json);

        $logger->pushProcessor(new PsrLogMessageProcessor());
        $logger->pushProcessor(new IntrospectionProcessor());
        $logger->pushProcessor(new WebProcessor());
        $logger->pushProcessor(new MemoryUsageProcessor());
        $logger->pushProcessor(new MemoryPeakUsageProcessor());

        return $logger;
    }
}