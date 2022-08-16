<?php

use App\Service\Mailer\Adapter\DebugMailAdapter;
use App\Service\Mailer\Adapter\MailgunAdapter;
use ConvertApi\ConvertApi;
use Firebase\JWT\JWT;
use Stripe\Stripe;

$env = [];

$env['displayErrorDetails'] = true;
$env['allowIndexingByBots'] = false;
$env['debug'] = false;
$env['basepath'] = '/';
$env['baseUrl'] = 'your-domain.dev'; // used whenever no request is present

$env['db']['database'] = 'your-app';
$env['db']['testing'] = 'your-app_test';
$env['db']['host'] = '127.0.0.1';
$env['db']['port'] = '3306';
$env['db']['username'] = 'root';
$env['db']['password'] = '';
$env['db']['migration']['username'] = 'root';
$env['db']['migration']['password'] = '';

$env[JWT::class]['secret_file']['path'] = __DIR__ . '/jwt/private.pem';
$env[JWT::class]['secret_file']['password'] = 'testing'; // this should not be committed to the repository, once it was changed
$env[JWT::class]['issuer'] = 'your-domain.com';
$env[JWT::class]['audience'] = 'your-domain.com';

$env[\Google\Client::class]['secret_file'] = __DIR__ . '/../data/config/oauth/client_secret.json';

$env['twig']['cache']['minify'] = false;
$env['twig']['cache']['enabled'] = true;
$env['twig']['debug'] = true;
$env['twig']['autoReload'] = true;

$env[MailgunAdapter::class]['from'] = '"Clippy" <contact@your-domain.ch>';
$env[MailgunAdapter::class]['api']['key'] = 'your-key';
$env[MailgunAdapter::class]['api']['endpoint'] = 'https://api.mailgun.net/v3/your-domain.mailgun.org';
$env[MailgunAdapter::class]['domain'] = 'your-domain.org';
$env[MailgunAdapter::class]['sandbox_to'] = 'your-email@your-domain.com';


return $env;
