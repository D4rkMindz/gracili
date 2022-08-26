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
     * @param string $userId
     * @param string $token
     * @param string $refreshToken
     * @param Moment $expiresAt
     * @param int|null $executorId
     *
     * @return bool
     */
    public function saveOAuthToken(
        string $userId,
        string $token,
        string $refreshToken,
        Moment $expiresAt,
        ?int $executorId = 0
    ): bool {
        $row = [
            'user_id' => $userId,
            'token' => $token,
            'refresh_token' => $refreshToken,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ];

        return (bool)$this->oAuthTokenTable->insert($row, $executorId)->lastInsertId();
    }

    /**
     * Archive all OAuth tokens of a user
     *
     * @param int $userId
     * @param int $executorId
     *
     * @return int
     */
    public function archiveTokensOfUser(int $userId, ?int $executorId = 0): int
    {
        return $this->oAuthTokenTable->archiveAll(['user_id' => $userId], $executorId);
    }

    /**
     * Delete all OAuth2.0 Tokens of a user
     *
     * USE WITH CAUTION! Should only be used on data delete requests
     *
     * @param int $userId
     *
     * @return int
     */
    public function deleteAllTokensOfUser(int $userId): int
    {
        return $this->oAuthTokenTable->deleteAll(['user_id' => $userId]);
    }
}