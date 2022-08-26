<?php

use App\Controller\Auth\LoginAction;
use App\Controller\Auth\LoginGoogleAction;
use App\Controller\Auth\LoginGoogleCallbackAction;
use App\Controller\Auth\RegisterAction;
use App\Controller\IndexAction;
use App\Queue\Extension\SignalHandler\AbortHandler;
use App\Queue\ProcessorInterface;
use App\Service\GeoIP\GeoIPService;
use App\Service\Mailer\Adapter\DebugMailAdapter;
use App\Service\Mailer\Adapter\MailerAdapterInterface;
use App\Service\Mailer\Adapter\MailgunAdapter;
use Firebase\JWT\JWT;
use Intervention\Image\Image;
use League\Flysystem\FilesystemInterface;
use Moment\Moment;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Translator;

ini_set("error_reporting", (string)(E_ALL & ~E_DEPRECATED));
Moment::setDefaultTimezone('Europe/Zurich'); // todo maybe set in middleware

$config = [];

$applicationName = 'your-app';

$config = [
    'name' => $applicationName,
    'displayErrorDetails' => true,
    'debug' => false,
    'determineRouteBeforeAppMiddleware' => true,
    'addContentLengthHeader' => false,
    'public' => __DIR__ . '/../public',
    'basepath' => '/',
    'baseUrl' => 'your-app.dev',
    'social' => [
        // all links without prefixed https:// since it is defined in the template
        'twitter' => 'twitter.com/your-app',
        'facebook' => 'www.facebook.com/your-app',
        'youtube' => 'www.youtube.com/channel/your-app',
        'instagram' => 'www.instagram.com/your-app/',
        'website' => 'your-domain.com',
    ],
    'contact' => [
        'name' => 'Your App',
        'email' => 'contact@your-domain.com',
    ],
];

$config['migrations'] = __DIR__ . '/../resources/migrations';
$config['seeds'] = __DIR__ . '/../resources/seeds';

$config['db'] = [
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'your-app',
    'testing' => 'your-app_test',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'encoding' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'migration' => [
        'username' => 'root',
        'password' => '',
    ],
];

$config[JWT::class] = [
    'secret_file' => ['path' => '', 'password' => ''], // usually in data/config/jwt/private.pem
    'header' => 'Authorization',
    'regexp' => '/(.*)$/i',
    'algorithm' => ['HS512'],
    'issuer' => 'your-domain.com',
    'audience' => 'your-domain.com',
    'expire' => 60 * 15, // 15 minutes
];

$config['auth'] = [
    'password_reset' => [
        'expiration' => 60 * 60 * 12, // 12 hours
        'email_sending_cooldown' => 60 * 5, // 5 minutes
    ],
    'relaxed' => [
        IndexAction::ROUTE => true,
        RegisterAction::NAME => true,
        LoginAction::NAME => true,
        LoginGoogleAction::NAME => true,
        LoginGoogleCallbackAction::NAME => true,
    ],
];

$config[\Google\Client::class] = [
    'application_name' => $applicationName,
    'scopes' => [
        'https://www.googleapis.com/auth/userinfo.email', // primary email address
        'https://www.googleapis.com/auth/userinfo.profile', // public user information
    ],
    'secret_file' => '', // usually in data/config/oauth/client_secret.json
    'redirect_uri' => 'https://cevi.dev/v1/auth/google/callback',
];

$config[Translator::class] = [
    'locale' => 'en_US',
    'path' => __DIR__ . '/../resources/locale',
];

$config['twig'] = [
    'path' => __DIR__ . '/../templates',
    'cache' => [
        'enabled' => true,
        'path' => __DIR__ . '/../tmp/cache/twig',
    ],
    'debug' => false,
    'autoReload' => false,
];

$root = __DIR__ . '/../data';

$config[FilesystemInterface::class] = [
    'root' => $root,
    'images' => '/images',
];

$filename = date('Y-m-d') . '/application';
$config[LoggerInterface::class] = [
    'file' => $root . '/logs/' . $filename,
    'stream' => $root . '/logs/' . $filename,
    'enqueue' => $root . '/enqueue/logs/' . $filename,
];
$config[Image::class] = [
    'max_file_size' => 1024 * 1024 * 5, // 5MB
    // only image types that are in the encoding of the image too are allowed
    // see http://image.intervention.io/api/encode for the list of available types
    'allowed_mime_types' => [
        'image/png' => 'png',
        'image/jpg' => 'jpg',
        'image/jpeg' => 'jpg',
    ],
];

$config[GeoIPService::class] = [
    // download on https://dev.maxmind.com/geoip/geolite2-free-geolocation-data
    'database' => $root . '/geoip/GeoLite2-City.mmdb',
];

$config[MailerAdapterInterface::class] = [
    'from' => '"Clippy" <contact@your-domain.com.org>',
    'contact' => 'contact@your-domain.com.org',
    'social' => [
        // all links without prefixed https:// since it is defined in the template
        'twitter' => 'twitter.com/your-app',
        'facebook' => 'www.facebook.com/your-app',
        'youtube' => 'www.youtube.com/channel/your-app',
        'instagram' => 'www.instagram.com/your-app/',
        'website' => 'your-domain.com',
    ],
];

$config[MailgunAdapter::class] = [
    'from' => '',
    'api' => [
        'key' => '',
        'endpoint' => '',
    ],
    'domain' => '',
    'sandbox_to' => '',
];

$config[DebugMailAdapter::class] = [
    'host' => '',
    'port' => 0,
    'username' => '',
    'password' => '',
];

$config[ProcessorInterface::class] = [
    'queue_path' => $root . '/queue',
];

$config[AbortHandler::class] = [
    'send_mail_on_abort' => true,
];

return $config;
