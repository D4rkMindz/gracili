<?php

namespace App\Service\Mailer;

use App\Service\Mailer\Adapter\MailerAdapterInterface;
use App\Service\SettingsInterface;
use Slim\Views\Twig;

/**
 * Class UserMailer
 */
class UserMailer extends AppMailer
{
    /**
     * Constructor.
     *
     * @param MailerAdapterInterface $mailerAdapter
     * @param Twig                   $twig
     * @param SettingsInterface      $settings
     */
    public function __construct(MailerAdapterInterface $mailerAdapter, Twig $twig, SettingsInterface $settings)
    {
        parent::__construct($mailerAdapter, $twig, $settings);
    }

    /**
     * Send registration email
     *
     * @param string $to
     * @param string $name
     * @param string $username
     */
    public function sendWelcomeEmail(string $to, string $name, string $username)
    {
        $viewData = [
            'username' => $username,
        ];

        $this->send(
            $to,
            __('Thank you for your registration'),
            'Email/Registration/registration',
            $viewData,
            $name
        );
    }

    /**
     * Send newsletter signup info
     *
     * @param string $to
     * @param string $name
     */
    public function sendNewsletterSignup(string $to, string $name)
    {
        $this->send(
            $to,
            __('Thank you for signing up for our newsletter'),
            'Email/Newsletter/newsletter-signup',
            [],
            $name
        );
    }
}
