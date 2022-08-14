<?php

use App\Middleware\ExceptionMiddleware;
use Slim\App;

return static function (App $app) {
    $app->addRoutingMiddleware();
    $app->addBodyParsingMiddleware();
    $app->add(ExceptionMiddleware::class);
};
