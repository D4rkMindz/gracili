<?php

namespace App\Service\Mailer;


interface MailerInterface
{
    /**
     * Get domain as string.
     *
     * @return string domain
     */
    public function getDomain(): string;
    /**
     * Send HTML email.
     *
     * @param string $to Receiver
     * @param string $from Forwarder
     * @param string $subject
     * @param string $html Email content as HTML
     * @return bool
     */
    public function sendHtml(string $to, string $subject, string $html, string $from = null): bool ;
    /**
     * Send Text email.
     *
     * @param string $to Receiver
     * @param string $from Forwarder
     * @param string $subject
     * @param string $text Email as String
     * @return bool
     */
    public function sendText(string $to, string $subject, string $text, string $from = null): bool ;
}
