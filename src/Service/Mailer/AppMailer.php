<?php

namespace App\Service\Mailer;

use App\Service\Mailer\Adapter\MailerAdapterInterface;
use App\Service\SettingsInterface;
use RuntimeException;
use Slim\Views\Twig;

/**
 * Class AppMailer
 */
abstract class AppMailer
{
    // all social media links (Facebook, Instagram, Twitter) with the according URL
    protected MailerAdapterInterface $mailer;
    protected Twig $twig;
    private array $socialMediaLinks;
    private string $from;
    private string $contact;
    private string $appName;

    protected function __construct(MailerAdapterInterface $mailerAdapter, Twig $twig, SettingsInterface $settings)
    {
        $this->mailer = $mailerAdapter;
        $this->twig = $twig;
        $mailgun = $settings->get(MailerAdapterInterface::class);
        $this->socialMediaLinks = $settings->get('social');
        $this->from = $mailgun['from'];
        $this->contact = $mailgun['contact'];
        $this->appName = $settings->get('name');
    }

    /**
     * Send an email
     *
     * @param string     $to
     * @param string     $subject
     * @param string     $templateWithoutExtension
     * @param array      $data Name always needs to be set manually
     * @param string     $name
     * @param array|null $attachments
     *
     * @throws RuntimeException
     */
    protected function send(
        string $to,
        string $subject,
        string $templateWithoutExtension,
        array $data,
        string $name,
        ?array $attachments = []
    ) {
        $data['title'] = $subject;
        $data['name'] = $name;
        $data['application_name'] = $this->appName;
        $data['contact'] = $this->contact;
        $data['social'] = $this->socialMediaLinks;

        $html = $this->twig->fetch($templateWithoutExtension . '.html.twig', $data);
        $text = $this->twig->fetch($templateWithoutExtension . '.txt.twig', $data);
        $sent = $this->mailer->send(
            $to,
            $subject,
            $html,
            $text,
            $this->from,
            $attachments
        );

        if (!$sent) {
            throw new RuntimeException(__('Sending an email failed'));
        }
    }
}