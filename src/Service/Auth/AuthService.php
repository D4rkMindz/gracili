<?php

namespace App\Service\Auth;

use App\Exception\AuthenticationException;
use App\Exception\RecordNotFoundException;
use App\Repository\PasswordResetRequestRepository;
use App\Repository\UserRepository;
use App\Type\HttpCode;
use Psr\Log\LoggerInterface;

/**
 * Class AuthService
 */
class AuthService
{
    private UserRepository $userRepository;
    private PasswordResetRequestRepository $passwordResetRequestRepository;
    private LoggerInterface $logger;

    /**
     * AuthService constructor.
     *
     * @param UserRepository                 $userRepository
     * @param PasswordResetRequestRepository $passwordResetRequestRepository
     * @param LoggerInterface                $logger
     */
    public function __construct(
        UserRepository $userRepository,
        PasswordResetRequestRepository $passwordResetRequestRepository,
        LoggerInterface $logger
    ) {
        $this->userRepository = $userRepository;
        $this->passwordResetRequestRepository = $passwordResetRequestRepository;
        $this->logger = $logger;
    }

    /**
     * Check if a user can log in.
     *
     * A user that signs in at a box has either to be the owner of the box or needs to have a security level of at
     * least 64 (ADMIN)
     *
     * @param int    $userId
     * @param string $password
     *
     * @return bool
     * @throws AuthenticationException
     */
    public function canLogin(int $userId, string $password): bool
    {
        try {
            $hash = $this->userRepository->getPassword($userId);

            return password_verify($password, $hash);
        } catch (RecordNotFoundException $exception) {
            throw new AuthenticationException(HttpCode::UNAUTHORIZED, __('Username or password invalid'));
        }
    }

    /**
     * Find or create a password reset request
     *
     * @param int $userId
     * @param int $executorId
     *
     * @return mixed
     */
    public function findOrCreatePasswordResetRequest(int $userId, int $executorId)
    {
        try {
            $hasPendingRequest = $this->passwordResetRequestRepository->hasPendingRequest($userId);
            if ($hasPendingRequest) {
                return $this->passwordResetRequestRepository->getPendingRequest($userId);
            }
        } catch (RecordNotFoundException $exception) {
            $this->logger->error('Pending password reset was found for user, but not loaded user_id=' . $userId);
        }

        $requestId = $this->passwordResetRequestRepository->createRequest($userId, $executorId);

        return $this->passwordResetRequestRepository->getRequestById($requestId);
    }

    /**
     * Get password reset request by token
     *
     * @param string $token
     *
     * @return mixed
     */
    public function getPasswordResetRequestByToken(string $token): array
    {
        return $this->passwordResetRequestRepository->getRequestByToken($token);
    }

    /**
     * Check if a password reset request is valid
     *
     * @param int $requestId
     *
     * @return bool
     */
    public function isValidPasswordResetRequest(int $requestId): bool
    {
        return $this->passwordResetRequestRepository->isRequestValid($requestId);
    }

    /**
     * @param int $requestId
     * @param int $userId
     *
     * @return bool
     */
    public function setPasswordResetRequestConfirmed(int $requestId, int $userId): bool
    {
        return $this->passwordResetRequestRepository->setConfirmed($requestId, $userId);
    }
}
