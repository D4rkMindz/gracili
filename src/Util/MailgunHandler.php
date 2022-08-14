<?php

namespace App\Util;

use App\Service\Mailer\EmergencyMailer;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Handler\MailHandler;
use Psr\Log\LogLevel;

class MailgunHandler extends MailHandler
{
    private EmergencyMailer $emergencyMailer;

    /**
     * Construct
     */
    public function __construct(EmergencyMailer $emergencyMailer)
    {
        parent::__construct(LogLevel::ERROR);
        $this->emergencyMailer = $emergencyMailer;
    }

    /**
     * Send handler
     *
     * @param       $content
     * @param array $records
     *
     * @return void
     */
    protected function send($content, array $records)
    {
        $highestRecord = $this->getHighestRecord($records);
        $subject = (new LineFormatter("[%datetime%] %channel%.%level_name%: %message%"))->format($highestRecord);
        $htmlFormatter = new HtmlFormatter(NormalizerFormatter::SIMPLE_DATE);

        $body = $htmlFormatter->formatBatch($records);

        $this->emergencyMailer->sendAlertToAdmin($subject, $body, $highestRecord['level_name']);
    }
}