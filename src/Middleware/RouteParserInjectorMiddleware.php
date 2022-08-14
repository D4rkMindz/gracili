<?php

namespace App\Middleware;

use DI\Container;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Slim\Interfaces\RouteParserInterface;
use Slim\Routing\RouteContext;

/**
 * Class RouteParserInjectorMiddleware
 */
class RouteParserInjectorMiddleware implements MiddlewareInterface
{
    /** @var ContainerInterface|Container */
    private ContainerInterface $container;

    /**
     * Constructor
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->container = $app->getContainer();
    }

    /**
     * Set the route parser into the container
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->container->has(RouteParserInterface::class)) {
            $parser = RouteContext::fromRequest($request)->getRouteParser();
            $this->container->set(RouteParserInterface::class, $parser);
        }

        return $handler->handle($request);
    }
}