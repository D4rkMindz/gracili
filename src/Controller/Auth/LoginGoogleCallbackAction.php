<?php

namespace App\Controller\Auth;

use App\Service\Auth\JWT\JWTService;
use App\Service\Auth\OAuth\AuthGoogleService;
use App\Service\Auth\OAuth\OAuthService;
use App\Service\Encoder\JSONEncoder;
use App\Service\User\UserService;
use App\Type\Language;
use App\Util\ArrayReader;
use Moment\Moment;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class LoginGoogleCallbackAction
 */
class LoginGoogleCallbackAction
{
    public const NAME = 'api.v1.auth.login.google.callback';

    private AuthGoogleService $google;
    private OAuthService $oAuth;
    private JSONEncoder $json;
    private JWTService $jwt;
    private UserService $user;

    /**
     * Constructor
     *
     * @param AuthGoogleService $google
     * @param OAuthService      $oAuth
     * @param UserService       $user
     * @param JWTService        $jwt
     * @param JSONEncoder       $json
     */
    public function __construct(
        AuthGoogleService $google,
        OAuthService $oAuth,
        UserService $user,
        JWTService $jwt,
        JSONEncoder $json
    ) {
        $this->google = $google;
        $this->oAuth = $oAuth;
        $this->user = $user;
        $this->jwt = $jwt;
        $this->json = $json;
    }

    /**
     * Invoke
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = new ArrayReader($request->getParsedBody());
        $userData = $this->google->getUserDataFromCode($data->findString('code'));

        $hasUserAccount = $this->user->hasUserAccountByEmail($userData->findString('email'));
        if ($hasUserAccount) {
            $userId = $this->user->getIdByUsername($userData->findString('email'));
        } else {
            $userId = $this->user->createOauthUser(
                $userData->findString('email'),
                $userData->findString('given_name'),
                $userData->findString('family_name'),
                $userData->findBool('email_verified'),
                Language::fromString($userData->findString('locale'))
            );
        }

        $jwtToken = $userData->findString('token_information.access_token');
        $refreshToken = $userData->findString('token_information.refresh_token');
        $createdAt = $userData->findInt('token_information.created');
        $expiresIn = $userData->findInt('token_information.expires_in');
        $expiresAt = new Moment('@' . ($createdAt + $expiresIn));

        $this->oAuth->saveOAuthToken($userId, $jwtToken, $refreshToken, $expiresAt);

        $jwt = $this->jwt->generateJWT($userId);
        $refreshToken = $this->jwt->createRefreshToken($jwt);
        $responseData['message'] = __('Login successful');
        $responseData['success'] = true;
        $responseData['jwt'] = $jwt;

        $responseData = [
            'message' => __('Login successful'),
            'success' => true,
            'jwt' => $jwt,
            'refresh_token' => $refreshToken,
        ];

        return $this->json->encode($response, $responseData);
    }
}