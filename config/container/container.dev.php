<?php

use App\Service\Mailer\Adapter\DebugMailAdapter;
use App\Service\Mailer\Adapter\MailerAdapterInterface;
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
): MailerAdapterInterface {
    return new DebugMailAdapter($settings);

    // $mailgun = $settings->get(MailgunAdapter::class);
    //
    // return new MailgunSandboxAdapter(
    //     $mailgun['api']['key'],
    //     $mailgun['api']['endpoint'],
    //     $mailgun['domain'],
    //     $mailgun['from'],
    //     $mailgun['sandbox_to'],
    //     $logger
    // );
};