<?php

use App\Service\Mailer\Adapter\MailerAdapterInterface;
use App\Service\Mailer\Adapter\MailgunAdapter;
use App\Service\SettingsInterface;
use App\Util\SimpleLogger;

/**
 * Mailer container.
 *
 * @param SimpleLogger      $logger
 * @param SettingsInterface $settings
 *
 * @return MailerAdapterInterface
 */
$container[MailerAdapterInterface::class] = static function (
    SimpleLogger $logger,
    SettingsInterface $settings
) {
    $mailgun = $settings->get(MailgunAdapter::class);

    return new MailgunAdapter(
        $mailgun['api']['key'],
        $mailgun['api']['endpoint'],
        $mailgun['domain'],
        $mailgun['from'],
        $logger
    );
};
