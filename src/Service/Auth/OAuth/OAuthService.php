<?php

namespace App\Service\Auth\OAuth;

use App\Repository\OAuthTokenRepository;
use Moment\Moment;

class OAuthService
{
    private OAuthTokenRepository $oAuthTokenRepository;

    /**
     * Constructor
     *
     * @param OAuthTokenRepository $OAuthTokenRepository
     */
    public function __construct(OAuthTokenRepository $OAuthTokenRepository)
    {
        $this->oAuthTokenRepository = $OAuthTokenRepository;
    }

    /**
     * Save OAuth Token
     *
     * @param int    $userId
     * @param string $token
     * @param string $refreshToken
     * @param Moment $expiresAt
     *
     * @return bool
     */
    public function saveOAuthToken(int $userId, string $token, string $refreshToken, Moment $expiresAt): bool
    {
        return $this->oAuthTokenRepository->saveOAuthToken($userId, $token, $refreshToken, $expiresAt, $userId);
    }
}