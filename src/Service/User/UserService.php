<?php

namespace App\Service\User;

use App\Repository\GroupRepository;
use App\Repository\LanguageRepository;
use App\Repository\UserRepository;
use App\Service\Validation\UserValidation;
use App\Type\Auth\Group;
use App\Type\Language;

/**
 * Class UserService
 */
class UserService
{
    private UserRepository $userRepository;
    private UserValidation $userValidation;
    private GroupRepository $groupRepository;
    private LanguageRepository $languageRepository;

    /**
     * UserService constructor.
     *
     * @param UserRepository     $userRepository
     * @param UserValidation     $userValidation
     * @param GroupRepository    $groupRepository
     * @param LanguageRepository $languageRepository
     */
    public function __construct(
        UserRepository $userRepository,
        UserValidation $userValidation,
        GroupRepository $groupRepository,
        LanguageRepository $languageRepository,
    ) {
        $this->userRepository = $userRepository;
        $this->userValidation = $userValidation;
        $this->groupRepository = $groupRepository;
        $this->languageRepository = $languageRepository;
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
            $firstName,
            $lastName,
            '',
            'oauth.google',
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
     * @param string      $firstName
     * @param string      $lastName
     * @param string      $password
     * @param string|null $languageTag
     * @param int|null    $executorId
     *
     * @return int
     */
    public function createUser(
        string $username,
        string $email,
        string $firstName,
        string $lastName,
        string $password,
        ?string $languageTag = Language::EN_GB,
        ?int $executorId = 0
    ): int {
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
            $firstName,
            $lastName,
            $password,
            'default',
            false,
            $executorId
        );

        $this->groupRepository->assignGroup($userId, Group::USER);

        return $userId;
    }

    /**
     * Modify an existing user
     *
     * @param int $userId
     * @param int $executorId
     * @param string|null $username
     * @param string|null $email
     * @param string|null $password
     * @param string|null $firstName
     * @param string|null $lastName
     * @param string|null $street
     * @param string|null $zip
     * @param string|null $city
     * @param bool|null $receiveNewsletter
     * @param bool|null $receiveLoginAlert
     *
     * @return bool
     */
    public function modifyUser(
        int $userId,
        int $executorId,
        ?string $username,
        ?string $email,
        ?string $password,
        ?string $firstName,
        ?string $lastName,
        ?string $street,
        ?string $zip,
        ?string $city,
        ?bool $receiveNewsletter,
        ?bool $receiveLoginAlert
    ) {
        $originalUser = $this->userRepository->getUserById($userId);

        $username = $originalUser['username'] === $username ? null : $username;
        $email = $originalUser['email'] === $email ? null : $email;
        $firstName = $originalUser['first_name'] === $firstName ? null : $firstName;
        $lastName = $originalUser['last_name'] === $lastName ? null : $lastName;
        $street = $originalUser['street'] === $street ? null : $street;
        $zip = $originalUser['zip'] === $zip ? null : $zip;
        $city = $originalUser['city'] === $city ? null : $city;
        $receiveNewsletter = ((bool)$originalUser['receive_newsletter']) === $receiveNewsletter ? null : $receiveNewsletter;
        $receiveLoginAlert = ((bool)$originalUser['receive_login_alert']) === $receiveLoginAlert ? null : $receiveLoginAlert;

        // receive newsletter or receive login alert do not need to be validated (always bool)
        $this->userValidation->validateModification(
            $userId,
            $executorId,
            $username,
            $email,
            $password,
            $firstName,
            $lastName,
            $street,
            $zip,
            $city
        );

        return $this->userRepository->modifyUser(
            $userId,
            $executorId,
            $username,
            $email,
            $firstName,
            $lastName,
            $street
        );
    }
}
