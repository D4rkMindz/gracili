<?php

namespace App\Middleware;

use App\Controller\AuthorizationInterface;
use App\Exception\AuthenticationException;
use App\Service\Auth\AuthorizationRules\AuthorizationRuleInterface;
use App\Service\Auth\JWT\JWTData;
use App\Service\Auth\JWT\JWTService;
use App\Service\SettingsInterface;
use App\Type\HttpCode;
use Firebase\JWT\JWT;
use HaydenPierce\ClassFinder\ClassFinder;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Slim\Routing\RouteContext;

/**
 * Class AuthMiddleware
 */
class AuthMiddleware implements MiddlewareInterface
{
    private JWTService $jwt;
    private ContainerInterface $container;
    private array $unsecureRoutes;
    private string $requestHeader;

    /**
     * Constructor
     *
     * @param JWTService        $jwt
     * @param SettingsInterface $settings
     * @param App               $app
     */
    public function __construct(
        JWTService $jwt,
        SettingsInterface $settings,
        App $app,
    ) {
        $this->jwt = $jwt;
        $this->container = $app->getContainer();
        $this->unsecureRoutes = $settings->get('auth')['relaxed'];
        $jwt = $settings->get(JWT::class);
        $this->requestHeader = $jwt['header'];
    }

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = RouteContext::fromRequest($request)->getRoute();
        $routeName = $route->getName();
        if (isset($this->unsecureRoutes[$routeName])) {
            return $handler->handle($request);
        }

        $header = $request->getHeaderLine($this->requestHeader);
        if (empty($header)) {
            $this->fail();
        }

        $token = '';
        $parts = explode(' ', $header);
        if (strtolower($parts[0]) === 'bearer' && isset($parts[1])) {
            $token = $parts[1];
        }

        // should the token be invalid, an authentication exception will be thrown
        // this also counts for expired tokens (although this will generate a different error message)
        $decoded = $this->jwt->decodeJWT($token);
        $request = $request->withAttribute(JWTData::REQUEST_ATTRIBUTE, [
            'token' => $token,
            'decoded' => $decoded,
            'data' => new JWTData($decoded['data']),
        ]);
        $implementations = class_implements($route->getCallable());
        if (!isset($implementations[AuthorizationInterface::class])) {
            throw new InvalidArgumentException('MISCONFIGURATION: Action ' . $route->getCallable() . ' does not have the authorization method implemented. Either add the AuthorizationInterface or define the Route as public');
        }

        $classes = ClassFinder::getClassesInNamespace('App\\Service\\Auth\\AuthorizationRules',
            ClassFinder::RECURSIVE_MODE);

        $override = false;
        foreach ($classes as $class) {
            /** @var AuthorizationRuleInterface $rule */
            $rule = $this->container->get($class);
            $ruleResult = $rule->process($request);
            if ($ruleResult) {
                $override = true;
            }
        }

        $canPass = false;
        if ($override !== true) {
            $callable = $this->container->get($route->getCallable());
            $canPass = call_user_func([$callable, 'authorize'], $request); // needs to instantiate the callable...
        }
        if ($override || $canPass === true) {
            return $handler->handle($request);
        }

        $this->fail();
    }

    /**
     * Fail and throw an authentication exception
     *
     * @return void
     * @throws AuthenticationException
     */
    private function fail(): void
    {
        throw new AuthenticationException(HttpCode::UNAUTHORIZED, __('Not authorized'));
    }
}
