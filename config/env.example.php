<?php
$env['displayErrorDetails'] = true;

$env['db']['host'] = '';
$env['db']['port'] = '';
$env['db']['username'] = '';
$env['db']['password'] = '';

$env['db_test']['host'] = '127.0.0.1';
$env['db_test']['port'] = '3306';
$env['db_test']['username'] = 'root';
$env['db_test']['password'] = '';

$env['mailgun']['from'] = 'noreply@gracili.com';
$env['mailgun']['apikey'] = 'your-key';
$env['mailgun']['domain'] = 'gracili.com';

$env['twig']['assetCache'] ['minify'] = false;
$env['twig']['assetCache'] ['cache_enabled'] = false;
$env['twig']['autoReload'] = true;

return $env;