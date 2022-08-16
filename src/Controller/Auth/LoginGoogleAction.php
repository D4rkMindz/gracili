<?php

namespace App\Controller\Auth;

use App\Service\Auth\OAuth\AuthGoogleService;
use App\Service\Encoder\JSONEncoder;
use Google\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class LoginGoogleAction
 */
class LoginGoogleAction
{
    public const NAME = 'api.v1.auth.login.google.submit';

    private AuthGoogleService $google;
    private JSONEncoder $json;

    /**
     * Constructor
     *
     * @param AuthGoogleService $google
     * @param JSONEncoder       $json
     */
    public function __construct(AuthGoogleService $google, JSONEncoder $json)
    {
        $this->google = $google;
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
        $uri = $this->google->getRedirectUrl();
        return $this->json->encode($response, [
            'message' => __('Redirecting you now'),
            'success' => true,
            'redirect' => $uri,
        ]);
    }
}