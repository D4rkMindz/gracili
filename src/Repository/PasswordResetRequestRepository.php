<?php

namespace App\Repository;

use App\Exception\RecordNotFoundException;
use App\Service\ID\UUID;
use App\Service\SettingsInterface;
use App\Table\PasswordResetRequestTable;
use Moment\Moment;

/**
 * Class PasswordResetRequestRepository
 */
class PasswordResetRequestRepository extends AppRepository
{
    private PasswordResetRequestTable $passwordResetRequestTable;
    private ?int $expiration;

    /**
     * Constructor
     *
     * @param PasswordResetRequestTable $passwordResetRequestTable
     * @param SettingsInterface         $settings
     */
    public function __construct(PasswordResetRequestTable $passwordResetRequestTable, SettingsInterface $settings)
    {
        $this->passwordResetRequestTable = $passwordResetRequestTable;
        $resetSettings = $settings->get('auth')['password_reset'];
        $this->expiration = $resetSettings['expiration'];
    }

    /**
     * Get a request by its ID
     *
     * @param int $id
     *
     * @return array
     */
    public function getRequestById(int $id): array
    {
        return $this->getRequestBy(['id' => $id]);
    }

    /**
     * Get a password reset request by a condition
     *
     * @param array $where
     *
     * @return array
     * @throws RecordNotFoundException
     */
    protected function getRequestBy(array $where): array
    {
        $query = $this->passwordResetRequestTable->newSelect();
        $query->select([
            'id',
            'user_id',
            'token',
            'expires_at',
            'email_sent_at',
            'confirmed_at',
        ])
            ->where($where);
        $result = $query->execute()->fetch('assoc');
        if (!empty($result)) {
            return $result;
        }

        throw new RecordNotFoundException('Password reset request not found', json_encode($where));
    }

    /**
     * Get a password reset request by its token
     *
     * @param string $token
     *
     * @return array
     */
    public function getRequestByToken(string $token): array
    {
        return $this->getRequestBy([
            'token' => $token,
        ]);
    }

    /**
     * Is request valid
     *
     * @param int $id
     *
     * @return bool
     */
    public function isRequestValid(int $id): bool
    {
        return $this->passwordResetRequestTable->exist([
            'id' => $id,
            'OR' => [
                'confirmed_at >= NOW()', // request must not be confirmed by now
                'confirmed_at IS NULL', // or must not be confirmed at all
            ],
            'expires_at >=' => (new Moment())->format('Y-m-d H:i:s'), // and the request must not be expired
        ]);
    }

    /**
     * Check if the user has a pending password reset request
     *
     * @param int $userId
     *
     * @return bool
     */
    public function hasPendingRequest(int $userId): bool
    {
        return $this->passwordResetRequestTable->exist([
            'user_id' => $userId,
            'expires_at >=' => (new Moment())->format('Y-m-d H:i:s'),
            'confirmed_at IS NULL',
        ]);
    }

    /**
     * Get the pending password reset request for a user
     *
     * @param int $userId
     *
     * @return array
     */
    public function getPendingRequest(int $userId): array
    {
        return $this->getRequestBy([
            'user_id' => $userId,
            'expires_at >=' => (new Moment())->format('Y-m-d H:i:s'),
            'confirmed_at IS NULL',
        ]);
    }

    /**
     * Create a password reset request
     *
     * @param int $userId
     * @param int $executorId
     *
     * @return int
     */
    public function createRequest(int $userId, int $executorId): int
    {
        $token = UUID::generate();
        $expiresAt = new Moment();
        $expiresAt->addSeconds($this->expiration);

        return $this->passwordResetRequestTable->insert([
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ])->lastInsertId();
    }

    /**
     * Set email sent at into the request
     *
     * @param int $requestId
     * @param int $executorId
     */
    public function setEmailSentAt(int $requestId, int $executorId)
    {
        $this->passwordResetRequestTable->update(
            ['email_sent_at' => (new Moment())->format('Y-m-d H:i:s')],
            ['id' => $requestId],
            $executorId
        );
    }

    /**
     * Set a password reset request as confirmed
     *
     * @param string $requestId
     * @param int    $executorId
     *
     * @return bool
     */
    public function setConfirmed(string $requestId, int $executorId): bool
    {
        return $this->passwordResetRequestTable->update(
            ['confirmed_at' => (new Moment())->format('Y-m-d H:i:s')],
            ['id' => $requestId],
            $executorId
        );
    }
}
