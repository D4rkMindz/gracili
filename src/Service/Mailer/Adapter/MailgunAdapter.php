<?php

namespace App\Service\Mailer\Adapter;

use Exception;
use Mailgun\Mailgun;
use Psr\Log\LoggerInterface;

/**
 * Class MailgunAdapter.
 */
class MailgunAdapter implements MailerAdapterInterface
{
    private Mailgun $mail;
    private string $domain;
    private string $from;
    private LoggerInterface $logger;

    /**
     * MailgunAdapter constructor.
     *
     * @param string          $key
     * @param string          $endpoint
     * @param string          $domain
     * @param string          $sender email
     * @param LoggerInterface $logger
     */
    public function __construct(string $key, string $endpoint, string $domain, string $sender, LoggerInterface $logger)
    {
        $this->mail = Mailgun::create($key, $endpoint);
        $this->domain = $domain;
        $this->from = $sender;
        $this->logger = $logger;
    }

    /**
     * Send Text email.
     *
     * @param string      $to   Receiver
     * @param string      $subject
     * @param string      $html
     *
     * @param string      $text Email as String
     * @param string|null $from Forwarder
     * @param array|null  $attachments
     *
     * @return bool
     */
    public function send(
        string $to,
        string $subject,
        string $html,
        string $text,
        string $from = null,
        ?array $attachments = []
    ): bool {
        if (empty($from)) {
            $from = $this->from;
        }
        $mailConfig = [
            'from' => $from,
            'to' => $to,
            'subject' => $subject,
            'text' => $text,
            'html' => $html,
        ];
        if (!empty($attachments)) {
            $mailConfig['attachment'] = $attachments;
        }

        try {
            $this->mail->messages()->send($this->getDomain(), $mailConfig);
        } catch (Exception $e) {
            $message = $e->getMessage();
            $message .= '\n';
            $message .= $e->getTraceAsString();
            $this->logger->error($message);

            return false;
        }

        return true;
    }

    /**
     * Get domain name.
     *
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }
}
