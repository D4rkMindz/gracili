<?php

namespace App\Controller\Auth;

use App\Exception\AuthenticationException;
use App\Exception\RecordNotFoundException;
use App\Service\Auth\AuthService;
use App\Service\Auth\JWT\JWTService;
use App\Service\Encoder\JSONEncoder;
use App\Service\User\UserService;
use App\Type\HttpCode;
use App\Util\ArrayReader;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Class LoginAction
 */
class LoginAction
{
    public const NAME = 'api.v1.auth.login.submit';

    private AuthService $auth;
    private UserService $user;
    private JWTService $jwt;
    private JSONEncoder $json;
    private LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param AuthService     $auth
     * @param UserService     $user
     * @param JWTService      $jwt
     * @param JSONEncoder     $json
     * @param LoggerInterface $logger
     */
    public function __construct(
        AuthService $auth,
        UserService $user,
        JWTService $jwt,
        JSONEncoder $json,
        LoggerInterface $logger
    ) {
        $this->auth = $auth;
        $this->user = $user;
        $this->jwt = $jwt;
        $this->json = $json;
        $this->logger = $logger;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $responseData = [
            'message' => __('Authentication failed'),
            'success' => false,
        ];

        $data = new ArrayReader($request->getParsedBody());

        $username = $data->findString('username', '');
        $password = $data->findString('password', '');
        $userId = null;
        try {
            $userId = $this->user->getIdByUsername($username);
        } catch (RecordNotFoundException $e) {
            $this->logger->info('Login failed', [
                'username' => $username,
                'password_empty' => empty($password),
                'exception' => [
                    'message' => $e->getMessage(),
                    'locator' => $e->getLocator(),
                    'trace' => $e->getTrace(),
                ],
            ]);
            $this->failLogin();
        }
        if (!$this->auth->canLogin($userId, $password)) {
            $this->failLogin();
        }

        $jwt = $this->jwt->generateJWT($userId);
        $refreshToken = $this->jwt->createRefreshToken($jwt);
        $responseData['message'] = __('Login successful');
        $responseData['success'] = true;
        $responseData['jwt'] = $jwt;
        $responseData['refresh_token'] = $refreshToken;

        return $this->json->encode($response, $responseData);
    }

    /**
     * Fails the login by throwing the proper exception
     *
     * @return void
     */
    private function failLogin()
    {
        throw new AuthenticationException(HttpCode::UNAUTHORIZED, __('Username or password invalid'));
    }
}