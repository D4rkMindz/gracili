<?php

use App\Service\Logger\Logger;
use App\Service\Mailer\MailerInterface;
use App\Service\Mailer\MailgunAdapter;
use App\Service\Validation\UserValidation;
use Odan\Twig\TwigTranslationExtension;
use Slim\Container;
use Slim\Exception\ContainerException;

$container = $app->getContainer();

/**
 * Mailer container.
 *
 * @param Container $container
 * @return MailgunAdapter
 * @throws Exception
 */
$container[MailerInterface::class] = function (Container $container) {
    try {
        $mailSettings = $container->get('settings')->get('mailgun');
        $mail = new MailgunAdapter($mailSettings['apikey'], $mailSettings['domain'], $mailSettings['from']);
    } catch (Exception $exception) {
        $logger = new Logger('Mailer');
        $message = $exception->getMessage();
        $message .= "\n" . $exception->getTraceAsString();
        $logger->error($message);
        throw new Exception('Mailer instantiation failed');
    }
    return $mail;
};

/**
 * Twig container.
 *
 * USED IN EMAIL RENDERING IN USERCONTROLLER!
 *
 * @param Container $container
 * @return Twig_Environment
 * @throws ContainerException
 */
$container[Twig_Environment::class] = function (Container $container): Twig_Environment {
    $twigSettings = $container->get('settings')->get('twig');
    $loader = new Twig_Loader_Filesystem($twigSettings['viewPath']);
    $twig = new Twig_Environment($loader, ['cache' => $twigSettings['cachePath']]);
    $twig->addExtension(new TwigTranslationExtension());
    return $twig;
};

/**
 * User validation container.
 *
 * @param Container $container
 * @return UserValidation
 */
$container[UserValidation::class] = function(Container $container) {
    return new UserValidation($container);
};