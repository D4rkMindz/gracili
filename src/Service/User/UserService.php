<?php

namespace App\Service\User;

use App\Repository\GroupRepository;
use App\Repository\JWTRepository;
use App\Repository\LanguageRepository;
use App\Repository\OAuthTokenRepository;
use App\Repository\PasswordResetRequestRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Service\Validation\UserValidation;
use App\Type\Auth\Group;
use App\Type\Language;
use App\Type\User\RegistrationMethod;

/**
 * Class UserService
 */
class UserService
{
    private UserRepository $userRepository;
    private UserValidation $userValidation;
    private GroupRepository $groupRepository;
    private RoleRepository $roleRepository;
    private LanguageRepository $languageRepository;
    private JWTRepository $jwtRepository;
    private OAuthTokenRepository $oAuthTokenRepository;
    private PasswordResetRequestRepository $passwordResetRequestRepository;

    /**
     * UserService constructor.
     *
     * @param UserRepository                 $userRepository
     * @param UserValidation                 $userValidation
     * @param GroupRepository                $groupRepository
     * @param RoleRepository                 $roleRepository
     * @param LanguageRepository             $languageRepository
     * @param JWTRepository                  $jwtRepository
     * @param OAuthTokenRepository           $oAuthTokenRepository
     * @param PasswordResetRequestRepository $passwordResetRequestRepository
     */
    public function __construct(
        UserRepository $userRepository,
        UserValidation $userValidation,
        GroupRepository $groupRepository,
        RoleRepository $roleRepository,
        LanguageRepository $languageRepository,
        JWTRepository $jwtRepository,
        OAuthTokenRepository $oAuthTokenRepository,
        PasswordResetRequestRepository $passwordResetRequestRepository,
    ) {
        $this->userRepository = $userRepository;
        $this->userValidation = $userValidation;
        $this->groupRepository = $groupRepository;
        $this->roleRepository = $roleRepository;
        $this->languageRepository = $languageRepository;
        $this->jwtRepository = $jwtRepository;
        $this->oAuthTokenRepository = $oAuthTokenRepository;
        $this->passwordResetRequestRepository = $passwordResetRequestRepository;
    }

    /**
     * Check if a user has a user account
     *
     * @param string $email
     *
     * @return bool
     */
    public function hasUserAccountByEmail(string $email): bool
    {
        return $this->userRepository->existsEmail($email);
    }

    /**
     * Get the user id based on the username
     *
     * @param string $username
     *
     * @return int
     */
    public function getIdByUsername(string $username): int
    {
        if (is_email($username)) {
            $userId = $this->userRepository->getIdBy('email', $username);
        } else {
            $userId = $this->userRepository->getIdBy('username', $username);
        }

        return $userId;
    }

    /**
     * Get all users
     *
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array
     */
    public function getAllUsers(?int $limit = 1000, ?int $offset = null): array
    {
        return $this->userRepository->getAllUsers($limit, $offset);
    }

    /**
     * Create a user that signed up using an oauth login
     *
     * @param string      $email
     * @param string      $firstName
     * @param string      $lastName
     * @param bool        $emailVerified
     * @param string|null $languageTag
     * @param int|null    $executorId
     *
     * @return int
     */
    public function createOauthUser(
        string $email,
        string $firstName,
        string $lastName,
        ?bool $emailVerified = false,
        ?string $languageTag = Language::EN_GB,
        ?int $executorId = 0
    ): int {
        $languageId = $this->languageRepository->getLanguageIdByTag($languageTag);
        $userId = $this->userRepository->createUser(
            $languageId,
            $email,
            $email,
            '',
            $firstName,
            $lastName,
            RegistrationMethod::OAUTH_GOOGLE,
            $emailVerified,
            $executorId
        );

        $this->groupRepository->assignGroup($userId, Group::USER);

        return $userId;
    }

    /**
     * Create a user
     *
     * @param string      $username
     * @param string      $email
     * @param string      $password
     * @param string      $firstName
     * @param string|null $lastName
     * @param string|null $languageTag
     * @param int|null    $executorId
     *
     * @return int
     */
    public function createUser(
        string $username,
        string $email,
        string $password,
        string $firstName,
        ?string $lastName,
        ?string $languageTag = Language::DEFAULT,
        ?int $executorId = 0
    ): int {
        if (empty($languageTag)) {
            $languageTag = Language::DEFAULT;
        }
        $languageId = $this->languageRepository->getLanguageIdByTag($languageTag);

        $this->userValidation->validateCreation(
            $email,
            $password,
            $firstName,
            $lastName
        );

        $userId = $this->userRepository->createUser(
            $languageId,
            $username,
            $email,
            $password,
            $firstName,
            $lastName,
            RegistrationMethod::DEFAULT,
            false,
            $executorId
        );

        $this->groupRepository->assignGroup($userId, Group::USER);

        return $userId;
    }

    /**
     * Modify an existing user
     *
     * @param int         $userId
     * @param int         $executorId
     * @param string|null $languageTag
     * @param string|null $username
     * @param string|null $email
     * @param string|null $password
     * @param string|null $firstName
     * @param string|null $lastName
     *
     * @return bool
     */
    public function modifyUser(
        int $userId,
        int $executorId,
        ?string $languageTag = null,
        ?string $username = null,
        ?string $email = null,
        ?string $password = null,
        ?string $firstName = null,
        ?string $lastName = null,
    ) {
        $originalUser = $this->userRepository->getUserById($userId);

        $username = $originalUser['username'] === $username ? null : $username;
        $email = $originalUser['email'] === $email ? null : $email;
        $firstName = $originalUser['first_name'] === $firstName ? null : $firstName;
        $lastName = $originalUser['last_name'] === $lastName ? null : $lastName;
        $languageId = null;
        if (!empty($languageTag)) {
            $languageId = $this->languageRepository->getLanguageIdByTag($languageTag);
            $languageId = ((int)$originalUser['language']['id']) === $languageId ? null : $languageId;
        }

        // receive newsletter or receive login alert do not need to be validated (always bool)
        $this->userValidation->validateModification(
            $userId,
            $username,
            $email,
            $password,
            $firstName,
            $lastName
        );

        $this->userRepository->modifyUser(
            $userId,
            $executorId,
            $languageId,
            $username,
            $email,
            $firstName,
            $lastName,
            $password
        );

        return $this->getUser($userId);
    }

    /**
     * Get a single user
     *
     * @param int $userId
     *
     * @return mixed
     */
    public function getUser(int $userId)
    {
        return $this->userRepository->getUserById($userId);
    }

    /**
     * Archive a user
     *
     * @param int      $userId
     * @param int|null $executorId
     *
     * @return bool
     */
    public function archive(int $userId, ?int $executorId = 0): bool
    {
        $this->jwtRepository->archiveAllJWTTokensOfUser($userId, $executorId);
        $this->oAuthTokenRepository->archiveTokensOfUser($userId, $executorId);
        $this->passwordResetRequestRepository->archiveAllPWResetRequestsForUser($userId, $executorId);
        $this->groupRepository->removeAllGroups($userId, $executorId);
        $this->roleRepository->removeAllRoles($userId, $executorId);

        return $this->userRepository->archiveUser($userId, $executorId);
    }

    /**
     * Delete a user and all its related data
     *
     * USE WITH CAUTION! Should only be used on data delete requests
     *
     * @param int $userId
     *
     * @return bool
     */
    public function delete(int $userId): bool
    {
        $this->jwtRepository->deleteAllJWTTokensOfUser($userId);
        $this->oAuthTokenRepository->deleteAllTokensOfUser($userId);
        $this->passwordResetRequestRepository->deleteAllPWResetRequestsForUser($userId);
        $this->groupRepository->deleteAllGroups($userId);
        $this->roleRepository->deleteAllRoles($userId);

        return $this->userRepository->deleteUser($userId);
    }
}
