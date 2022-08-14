<?php

namespace App\Service\Mailer\Adapter;

use DomainException;
use Exception;
use Mailgun\Exception\HttpClientException;
use Mailgun\Mailgun;
use Psr\Log\LoggerInterface;

/**
 * Class MailgunAdapter.
 */
class MailgunSandboxAdapter implements MailerAdapterInterface
{
    private Mailgun $mail;
    private string $domain;
    private string $from;
    private string $to;
    private LoggerInterface $logger;

    /**
     * MailgunAdapter constructor.
     *
     * @param string          $key
     * @param string          $endpoint
     * @param string          $domain
     * @param string          $sender email
     * @param string          $to     is always the same, since sandbox mode does not allow sending to other emails
     *                                than the configured ones
     * @param LoggerInterface $logger
     */
    public function __construct(
        string $key,
        string $endpoint,
        string $domain,
        string $sender,
        string $to,
        LoggerInterface $logger
    ) {
        $this->mail = Mailgun::create($key, $endpoint);
        $this->domain = $domain;
        $this->from = $sender;
        $this->to = $to;
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
            'to' => $this->to,
            'subject' => $subject,
            'text' => $text,
            'html' => $html,
        ];
        if (!empty($attachments)) {
            $mailConfig['attachment'] = $attachments;
        }

        try {
            $this->mail->messages()->send($this->getDomain(), $mailConfig);
        } catch (HttpClientException $httpException) {
            if ($httpException->getResponseCode() === 401) {
                throw new DomainException('Mailgun token does not work', 401, $httpException);
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            $message .= '\n';
            $message .= $e->getTraceAsString();
            $this->logger->critical($message, [
                'message' => $message,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

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
