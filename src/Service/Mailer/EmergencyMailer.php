<?php

namespace App\Service\Mailer;

use App\Service\Mailer\Adapter\MailgunAdapter;
use App\Service\Mailer\Adapter\MailgunSandboxAdapter;
use App\Service\SettingsInterface;
use App\Util\SimpleLogger;
use Moment\Moment;
use Slim\Views\Twig;

/**
 * Class EmergencyMailer
 */
class EmergencyMailer extends AppMailer
{
    private string $defaultTo;

    /**
     * Construct
     *
     * @param Twig              $twig
     * @param SettingsInterface $settings
     * @param SimpleLogger      $logger
     */
    public function __construct(Twig $twig, SettingsInterface $settings, SimpleLogger $logger)
    {
        // always use the sandbox for admin emails to save email processing fees
        $mailgun = $settings->get(MailgunAdapter::class);

        $this->defaultTo = $mailgun['sandbox_to'];

        $mailerAdapter = new MailgunSandboxAdapter(
            $mailgun['api']['key'],
            $mailgun['api']['endpoint'],
            $mailgun['domain'],
            $mailgun['from'],
            $mailgun['sandbox_to'],
            $logger
        );

        parent::__construct($mailerAdapter, $twig, $settings);
    }

    /**
     * Send an alert
     *
     * @param string      $title
     * @param string      $message
     * @param string|null $level
     *
     * @return void
     */
    public function sendAlertToAdmin(string $title, string $message, ?string $level = 'emergency')
    {
        $this->send(
            $this->defaultTo,
            '[' . strtoupper($level) . ' RECEIVED] on ' . APP_ENV,
            'Email/Admin/alert',
            [
                'title' => $title,
                'message' => $message,
                'now' => (new Moment())->format('d.m.Y H:i:s'),
                'environment' => APP_ENV,
            ],
            'Admin'
        );
    }
}
