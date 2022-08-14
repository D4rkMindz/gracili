<?php

use App\Controller\Auth\LoginAction;
use App\Controller\IndexAction;
use App\Controller\Monitor\MonitorQueueAction;
use App\Middleware\AuthMiddleware;
use App\Middleware\LanguageMiddleware;
use App\Middleware\RouteParserInjectorMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return static function (App $app) {
    $container = $app->getContainer();
    $app->group('', function (RouteCollectorProxy $group) {
        $regex = '[a-zA-Z0-9]*';

        $group->get('/', IndexAction::class)->setName(IndexAction::ROUTE);

        $group->group('/v1', function (RouteCollectorProxy $v1) {
            $v1->group('/auth', function (RouteCollectorProxy $auth) {
                $auth->post('/login', LoginAction::class)->setName(LoginAction::ROUTE);
            });

            $v1->group('/monitoring', function (RouteCollectorProxy $monitoring) {
                $monitoring->get('/queue', MonitorQueueAction::class)->setName(MonitorQueueAction::NAME);
            });
        });
    })
        // exception middleware is added in middleware.php
        ->addMiddleware($container->get(RouteParserInjectorMiddleware::class))
        ->addMiddleware($container->get(LanguageMiddleware::class))
        ->addMiddleware($container->get(AuthMiddleware::class));
};
