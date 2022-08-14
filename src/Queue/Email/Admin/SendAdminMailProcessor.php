<?php

namespace App\Queue\Email\Admin;

use App\Queue\AbstractProcessor;
use App\Service\ID\UUID;
use App\Service\Mailer\EmergencyMailer;
use App\Util\SimpleLogger;
use Interop\Queue\Context;
use Interop\Queue\Message;

class SendAdminMailProcessor extends AbstractProcessor
{
    public const KEY_SUBJECT = 'subject';
    public const KEY_BODY = 'body';
    public const KEY_LEVEL = 'level';
    private EmergencyMailer $mailer;

    /**
     * Construct
     *
     * @param SimpleLogger $logger
     * @param EmergencyMailer $mailer
     */
    public function __construct(SimpleLogger $logger, EmergencyMailer $mailer)
    {
        parent::__construct($logger);
        $this->mailer = $mailer;
    }

    /**
     * @param Message $message
     * @param Context $context
     *
     * @return string
     */
    public function process(Message $message, Context $context): string
    {
        $data = json_decode($message->getBody(), true);
        $level = $data[self::KEY_LEVEL] ?? "emergency";
        $contextHash = UUID::generate();
        $this->logger->info(
            'Sending log info on level ' . $level . ' to admin',
            ['data' => $message->getBody(), 'context' => $contextHash]
        );

        $this->mailer->sendAlertToAdmin($data[self::KEY_SUBJECT], $data[self::KEY_BODY], $level);

        $this->logger->info(
            'Sent log info on level ' . $level . ' to admin',
            ['context' => $contextHash]
        );

        return self::ACK;
    }
}