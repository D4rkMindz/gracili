<?php

namespace App\Service\Mailer\Adapter;

/**
 * Class NullMailAdapter
 */
class NullMailAdapter implements MailerAdapterInterface
{
    /**
     * Send HTML email.
     *
     * @param string      $to   Receiver
     * @param string      $subject
     * @param string      $html Email content as HTML
     * @param string      $text
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
        return true;
    }
}
