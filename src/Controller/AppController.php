<?php

namespace App\Controller;

use App\Service\Logger\Logger;
use App\Util\ValidationResult;
use Interop\Container\Exception\ContainerException;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Router;
use Slim\Views\Twig;
use Twig_Environment;

/**
 * Class AppController.
 */
abstract class AppController
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Twig_Environment
     */
    protected $twig;


    /**
     * AppController constructor.
     *
     * @param Container $container
     *
     * @throws ContainerException
     */
    public function __construct(Container $container)
    {
        $this->request = $container->get('request');
        $this->response = $container->get('response');
        $this->router = $container->get('router');
        $this->logger = new Logger('application');
        $this->twig = $container->get(Twig_Environment::class);
    }

    /**
     * Return JSON Response.
     *
     * @param Response $response
     * @param array $data
     * @param int $status
     *
     * @return ResponseInterface
     */
    protected function json(Response $response, $data, int $status = 200): ResponseInterface
    {
        $data['success'] = array_value('success', $data) ?: true;
        return $response->withJson($data, $status);
    }

    /**
     * Return redirect.
     *
     * @param Response $response
     * @param string $url
     * @param int $status
     *
     * @return ResponseInterface
     */
    protected function redirect(Response $response, string $url, int $status = 301): ResponseInterface
    {
        return $response->withRedirect($url, $status);
    }

    /**
     * Return a validation error.
     *
     * @param Response $response
     * @param ValidationResult $validationContext
     * @param int $status
     * @return ResponseInterface
     */
    protected function validationError(Response $response, ValidationResult $validationContext, int $status = 422): ResponseInterface
    {
        return $this->error($response, $validationContext->toArray(), $status);
    }

    /**
     * Return an error.
     *
     * @param Response $response
     * @param array $data
     * @param int $status
     * @return ResponseInterface
     */
    protected function error(Response $response, array $data, int $status = 422): ResponseInterface
    {
        $data['success'] = array_value('success', $data) ?: false;
        return $this->json($response, $data, $status);
    }

    /**
     * Get the parsed body from JSON.
     *
     * @param Request $request
     * @return mixed
     */
    protected function getParsedBody(Request $request)
    {
        $json = $request->getBody()->__toString();
        return json_decode($json, true);
    }

    /**
     * Get the current user id.
     *
     * @return string
     */
    protected function getCurrentUserId()
    {
        // TODO
        return "1";
    }
}
