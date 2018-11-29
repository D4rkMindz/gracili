<?php

namespace App\Controller;


use Interop\Container\Exception\ContainerException;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class AuthenticationController
 */
class AuthenticationController extends AppController
{
    /**
     * AuthenticationController constructor.
     * @param Container $container
     * @throws ContainerException
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    /**
     * Login action
     *
     * @param Request $request
     * @param Response $response
     * @return ResponseInterface
     */
    public function loginAction(Request $request, Response $response): ResponseInterface
    {
        return $this->json($response, ['message' => __('Not implemented yet'), 500]);
    }
}
