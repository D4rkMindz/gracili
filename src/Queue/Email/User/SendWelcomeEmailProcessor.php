<?php

namespace App\Queue\Email\User;

use App\Queue\AbstractProcessor;
use App\Service\ID\UUID;
use App\Service\Mailer\UserMailer;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Psr\Log\LoggerInterface;

/**
 * Class SendWelcomeEmailProcessor
 */
class SendWelcomeEmailProcessor extends AbstractProcessor
{
    public const KEY_EMAIL = 'email';
    public const KEY_FIRST_NAME = 'first_name';
    public const KEY_USERNAME = 'username';

    private UserMailer $userMailer;

    /**
     * Constructor
     *
     * @param UserMailer $userMailer
     * @param LoggerInterface $logger
     */
    public function __construct(UserMailer $userMailer, LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->userMailer = $userMailer;
    }

    /**
     * Handler
     *
     * @param Message $message
     * @param Context $context
     *
     * @return string
     */
    public function process(Message $message, Context $context): string
    {
        $data = json_decode($message->getBody(), true);
        $contextHash = UUID::generate();
        $this->logger->info(
            'Sending user ' . $data[self::KEY_EMAIL] . ' a welcome email',
            ['data' => $message->getBody(), 'context' => $contextHash]
        );

        $this->userMailer->sendWelcomeEmail($data[self::KEY_EMAIL],
            $data[self::KEY_FIRST_NAME],
            $data[self::KEY_USERNAME]);

        $this->logger->info(
            'Welcome email sent to user' . $data[self::KEY_EMAIL],
            ['context' => $contextHash]
        );

        return self::ACK;
    }
}