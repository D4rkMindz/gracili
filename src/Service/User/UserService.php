<?php

namespace App\Service\User;

use App\Exception\RecordNotFoundException;
use App\Repository\UserRepository;
use App\Service\Validation\UserValidation;

/**
 * Class UserService
 */
class UserService
{
    private UserRepository $userRepository;
    private UserValidation $userValidation;

    /**
     * UserService constructor.
     *
     * @param UserRepository $userRepository
     * @param UserValidation $userValidation
     */
    public function __construct(UserRepository $userRepository, UserValidation $userValidation)
    {
        $this->userRepository = $userRepository;
        $this->userValidation = $userValidation;
    }

    /**
     * Get the user id based on the username
     *
     * @param string $username
     *
     * @return int
     */
    public function getIdByUsername(string $username)
    {
        if (is_email($username)) {
            $userId = $this->userRepository->getIdBy('email', $username);
        } else {
            $userId = $this->userRepository->getIdBy('username', $username);
        }

        return $userId;
    }

    public function getUserData(int $userId, string $key)
    {

    }

    /**
     * Create a user
     *
     * @param string $username
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $password
     * @param bool   $acceptedTC
     * @param int    $executorId
     *
     * @return int
     */
    public function createUser(
        string $username,
        string $email,
        string $firstName,
        string $lastName,
        string $password,
        bool $acceptedTC,
        int $executorId
    ): int {
        $this->userValidation->validateCreation(
            $email,
            $password,
            $firstName,
            $lastName,
            $acceptedTC
        );

        return $this->userRepository->createUser(
            $username,
            $email,
            $firstName,
            $lastName,
            $password,
            $executorId
        );
    }

    /**
     * Modify an existing user
     *
     * @param int         $userId
     * @param int         $executorId
     * @param string|null $username
     * @param string|null $email
     * @param string|null $password
     * @param string|null $firstName
     * @param string|null $lastName
     * @param string|null $street
     * @param string|null $zip
     * @param string|null $city
     * @param bool|null   $receiveNewsletter
     * @param bool|null   $receiveLoginAlert
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

    /**
     * Get a user
     *
     * @param int $userId
     *
     * @return mixed
     * @throws RecordNotFoundException
     */
    public function getUser(int $userId)
    {
        return $this->userRepository->getUserById($userId);
    }
}
