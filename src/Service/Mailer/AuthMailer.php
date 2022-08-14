<?php

namespace App\Service\Mailer;

use App\Repository\PasswordResetRequestRepository;
use App\Service\Mailer\Adapter\MailerAdapterInterface;
use App\Service\SettingsInterface;
use Moment\Moment;
use Slim\Views\Twig;

/**
 * Class AuthMailer
 */
class AuthMailer extends AppMailer
{
    private PasswordResetRequestRepository $passwordResetRequestRepository;

    /**
     * Constructor.
     *
     * @param MailerAdapterInterface         $mailerAdapter
     * @param Twig                           $twig
     * @param SettingsInterface              $settings
     * @param PasswordResetRequestRepository $passwordResetRequestRepository
     */
    public function __construct(
        MailerAdapterInterface $mailerAdapter,
        Twig $twig,
        SettingsInterface $settings,
        PasswordResetRequestRepository $passwordResetRequestRepository
    ) {
        parent::__construct($mailerAdapter, $twig, $settings);
        $this->passwordResetRequestRepository = $passwordResetRequestRepository;
    }

    /**
     * Send login email
     *
     * @param string $to
     * @param string $name
     * @param string $ip
     * @param string $location
     */
    public function sendLoginEmail(string $to, string $name, string $ip, string $location)
    {
        $viewData = [
            'ip' => $ip,
            'time' => date('d.m.Y H:i'),
            'location' => $location,
        ];

        $this->send(
            $to,
            __('Recent login'),
            'Email/Login/login',
            $viewData,
            $name
        );
    }

    /**
     * Send a password reset email
     *
     * @param string $to
     * @param string $name
     * @param int    $requestId
     * @param string $resetLink
     * @param string $expiresAt
     * @param int    $executorId
     */
    public function sendPasswordResetEmail(
        string $to,
        string $name,
        int $requestId,
        string $resetLink,
        string $expiresAt,
        int $executorId
    ) {
        $resetLink = str_replace('https://', '', $resetLink);
        $viewData = [
            'reset_link' => $resetLink,
            'expires_at' => (new Moment($expiresAt))->format('d.m.Y (H:i)'),
        ];

        $this->send($to, __('Password reset'), 'Email/PasswordReset/reset-password', $viewData, $name);
        $this->passwordResetRequestRepository->setEmailSentAt($requestId, $executorId);
    }
}
