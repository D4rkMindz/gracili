<?php

namespace App\Queue\Extension\SignalHandler;

use App\Queue\Extension\SignalExtension;
use App\Queue\Extension\SignalListener;
use App\Service\Mailer\EmergencyMailer;
use App\Service\SettingsInterface;
use Psr\Log\LoggerInterface;

/**
 * Class AbortHandler
 */
class AbortHandler implements HandlerInterface
{
    private EmergencyMailer $mailer;
    private bool $sendEmailOnAbort;

    /**
     * Constructor
     *
     * @param EmergencyMailer   $mailer
     * @param SettingsInterface $settings
     */
    public function __construct(EmergencyMailer $mailer, SettingsInterface $settings)
    {
        $this->mailer = $mailer;
        $this->sendEmailOnAbort = $settings->get(AbortHandler::class)['send_mail_on_abort'];
    }

    /**
     * Handler
     *
     * @param LoggerInterface $logger
     * @param int             $signal
     *
     * @return string
     */
    public function __invoke(LoggerInterface $logger, int $signal): string
    {
        // TODO maybe only log an emergency in case of an unexpected stop...
        $logger->emergency('Enqueue was stopped! Please restart (signal = ' . $signal . ')');
        if ($this->sendEmailOnAbort) {
            $this->mailer->sendAlertToAdmin('Enqueue was stopped', 'Please restart enqueue');
        }

        return (string)$signal;
    }

    /**
     * Convert to a listener
     *
     * @return SignalListener
     */
    public function toListener(): SignalListener
    {
        return new SignalListener(self::class, SignalExtension::ABORT, $this, 0);
    }
}