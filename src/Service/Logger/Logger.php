<?php

namespace App\Service\Logger;


use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger as Monolog;

class Logger
{
    /**
     * @var Monolog
     */
    private $logger;

    /**
     * @var string
     */
    private $domain;

    /**
     * Logger constructor.
     *
     * @param string $domain Where the logger is executed
     */
    public function __construct(string $domain)
    {
        $this->domain = strtolower($domain);
        $path = __DIR__ . '/../../../tmp/logs/';
        $date = date('Y-m-d');

        $filename = "{$date}_{$this->domain}.log";
        $fileHandler = new RotatingFileHandler($path . $filename);

        $this->logger = new Monolog($domain, [$fileHandler]);
        $this->domain = $domain;
    }

    /**
     * Info.
     *
     * @param string $message
     */
    public function info(string $message)
    {
        $this->logger->info($this->formatMessage($message));
    }

    /**
     * Debug.
     *
     * @param string $message
     */
    public function debug(string $message)
    {
        $this->logger->debug($this->formatMessage($message));
    }

    /**
     * Warning.
     *
     * @param string $message
     */
    public function warn(string $message)
    {
        $this->logger->warn($this->formatMessage($message));
    }

    /**
     * Error.
     *
     * @param string $message
     */
    public function error(string $message)
    {
        $this->logger->error($this->formatMessage($message));
    }

    /**
     * Generate the message
     *
     * @param $message
     * @return string
     */
    private function formatMessage($message)
    {
        // Here is the place to manipulate the message
        return $message;
    }
}
