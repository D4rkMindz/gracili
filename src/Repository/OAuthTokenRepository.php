<?php

namespace App\Repository;

use App\Table\OAuthTokenTable;
use Moment\Moment;

/**
 * Class OAuthTokenRepository
 */
class OAuthTokenRepository extends AppRepository
{
    private OAuthTokenTable $oAuthTokenTable;

    /**
     * Constructor
     * 
     * @param OAuthTokenTable $OAuthTokenTable
     */
    public function __construct(OAuthTokenTable $OAuthTokenTable)
    {
        $this->oAuthTokenTable = $OAuthTokenTable;
    }

    /**
     * Save the OAuth token received from a OAuth2.0 provider (e.g. Google or Apple)
     *
     * @param string   $userId
     * @param string   $token
     * @param string   $refreshToken
     * @param Moment   $expiresAt
     * @param int|null $executorId
     *
     * @return bool
     */
    public function saveOAuthToken(string $userId, string $token, string $refreshToken, Moment $expiresAt, ?int $executorId = 0): bool
    {
        $row = [
            'user_id' => $userId,
            'token' => $token,
            'refresh_token' => $refreshToken,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ];
        return (bool) $this->oAuthTokenTable->insert($row, $executorId)->lastInsertId();
    }
}