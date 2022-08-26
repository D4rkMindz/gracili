<?php

namespace App\Repository;

use App\Exception\RecordNotFoundException;
use App\Table\JWTTable;
use Moment\Moment;

/**
 * JWTRepository
 */
class JWTRepository extends AppRepository
{
    private JWTTable $jwtTable;

    /**
     * Constructor
     *
     * @param JWTTable $jwtTable
     */
    public function __construct(JWTTable $jwtTable)
    {
        $this->jwtTable = $jwtTable;
    }

    /**
     * Save the JWT / refresh token generated by the application
     *
     * @param int      $userId
     * @param string   $jwt
     * @param string   $refreshToken
     * @param Moment   $issuedAt
     * @param Moment   $expiresAt
     * @param int|null $executorId
     *
     * @return bool
     */
    public function saveJWTToken(
        int $userId,
        string $jwt,
        string $refreshToken,
        Moment $issuedAt,
        Moment $expiresAt,
        ?int $executorId = 0
    ): bool {
        $row = [
            'user_id' => $userId,
            'jwt' => $jwt,
            'refresh_token' => $refreshToken,
            'issued_at' => $issuedAt->format('Y-m-d H:i:s'),
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ];

        return (bool)$this->jwtTable->insert($row, $executorId)->lastInsertId();
    }

    /**
     * Check if a user can refresh its JWT token
     *
     * @param int    $userId
     * @param string $jwt
     * @param string $refreshToken
     *
     * @return Moment
     * @throws RecordNotFoundException
     */
    public function getExpiresAt(int $userId, string $jwt, string $refreshToken): Moment
    {
        $query = $this->jwtTable->newSelect();
        $query->select(['expires_at'])
            ->where([
                'user_id' => $userId,
                'jwt' => $jwt,
                'refresh_token' => $refreshToken,
            ]);
        $result = $query->execute()->fetch('assoc');
        if (!empty($result)) {
            return new Moment($result['expires_at']);
        }

        throw new RecordNotFoundException(__('Token not found'), 'user_id = ' . $userId);
    }

    /**
     * Archive all JWT Tokens of a user
     *
     * @param int      $userId
     * @param int|null $executorId
     *
     * @return int
     */
    public function archiveAllJWTTokensOfUser(int $userId, ?int $executorId = 0): int
    {
        return $this->jwtTable->archiveAll(['user_id' => $userId], $executorId);
    }

    /**
     * Delete all JWT Tokens of a user
     *
     * USE WITH CAUTION! Should only be used on data delete requests
     *
     * @param int $userId
     *
     * @return int
     */
    public function deleteAllJWTTokensOfUser(int $userId): int
    {
        return $this->jwtTable->deleteAll(['user_id' => $userId]);
    }
}