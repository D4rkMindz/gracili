<?php

namespace App\Service\Validation;

use App\Repository\UserRepository;
use App\Util\ValidationResult;

/**
 * Class UserValidation
 */
class UserValidation extends AppValidation
{
    private UserRepository $userRepository;

    /**
     * UserValidation constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(
        UserRepository $userRepository
    ) {
        $this->userRepository = $userRepository;
    }

    /**
     * Validate the creation of a user
     *
     * @param string $email
     * @param string $password
     * @param string $firstName
     * @param string $lastName
     * @param bool   $acceptTC
     */
    public function validateCreation(
        string $email,
        string $password,
        string $firstName,
        string $lastName,
        bool $acceptTC
    ) {
        $validationResult = new ValidationResult(__('Please check your data'));

        $this->validateEmail($email, $validationResult);
        $this->validatePassword($password, $validationResult);
        $this->validateName($firstName, 'first_name', $validationResult);
        $this->validateName($lastName, 'last_name', $validationResult);
        $this->validateAcceptedTC($acceptTC, $validationResult);

        $this->throwOnError($validationResult);
    }

    /**
     * Validate the email
     *
     * @param string           $email
     * @param ValidationResult $validationResult
     */
    private function validateEmail(
        string $email,
        ValidationResult $validationResult
    ) {
        if (!is_email($email)) {
            $validationResult->setError('email', __('Email is not valid'));
        }
        if ($this->userRepository->existsEmail($email, null)) {
            // TODO Maybe inform the real user about the sign up attempt -> userdata key
            $validationResult->setError('email', __('Email is already registered.'));
        }
    }

    /**
     * Validate the password
     *
     * @param string           $password
     * @param ValidationResult $validationResult
     */
    public function validatePassword(
        string $password,
        ValidationResult $validationResult
    ) {
        $this->validateLengthMin($password, 'password', $validationResult, 8);
        $this->validateLengthMax($password, 'password', $validationResult, 65);

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&+])[A-Za-z\d@$!%*?&+]{8,}$/', $password)) {
            $validationResult->setError(
                'password',
                __('Password must contain at least one uppercase letter, one lowercase letter, one number and one special character (@$!%*?&+)')
            );
        }
    }

    /**
     * Validate the username
     *
     * @param string           $name
     * @param string           $field
     * @param ValidationResult $validationResult
     */
    private function validateName(
        string $name,
        string $field,
        ValidationResult $validationResult
    ): void {
        $this->validateLengthMin($name, $field, $validationResult, 3);
        $this->validateLengthMax($name, $field, $validationResult, 50);
    }

    /**
     * Validate that the user accepted the terms and conditions
     *
     * @param bool             $acceptedTC
     * @param ValidationResult $validationResult
     */
    private function validateAcceptedTC(bool $acceptedTC, ValidationResult $validationResult)
    {
        if (!$acceptedTC) {
            $validationResult->setError('accept_tc', __('You must accept the terms and conditions to sign up'));
        }
    }

    /**
     * Validate the modification of a user
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
     */
    public function validateModification(
        int $userId,
        int $executorId,
        ?string $username,
        ?string $email,
        ?string $password,
        ?string $firstName,
        ?string $lastName,
        ?string $street,
        ?string $zip,
        ?string $city
    ) {
        $validationResult = new ValidationResult(__('Please check your data'));

        if ($userId !== $executorId) {
            $validationResult->setError('user', __('You cannot edit this user!'));
            $this->throwOnError($validationResult);

            return;
        }

        if (!empty($username)) {
            $this->validateUsername($username, $validationResult);
        }
        if (!empty($email)) {
            $this->validateEmailUpdate($email, $userId, $validationResult);
        }
        if (!empty($password)) {
            $this->validatePassword($password, $validationResult);
        }
        if (!empty($firstName)) {
            $this->validateName($firstName, 'first_name', $validationResult);
        }
        if (!empty($lastName)) {
            $this->validateName($lastName, 'last_name', $validationResult);
        }
        if (!empty($street)) {
            $this->validateStreet($street, $validationResult);
        }
        if (!empty($zip)) {
            $this->validateZip($zip, $validationResult);
        }
        if (!empty($city)) {
            $this->validateCity($city, $validationResult);
        }

        $this->throwOnError($validationResult);
    }

    /**
     * Validate the username
     *
     * @param string           $username
     * @param ValidationResult $validationResult
     */
    private function validateUsername(
        string $username,
        ValidationResult $validationResult
    ): void {
        $reservedWords = [
            'admin',
            'administrator',
            'shit',
            'bullshit',
            'fuck',
            'asshole',
            'looser',
        ];

        $this->validateLengthMin($username, 'username', $validationResult, 3);
        $this->validateLengthMax($username, 'username', $validationResult, 50);

        if ($this->userRepository->existsUsername($username)) {
            $validationResult->setError('username', __('Username already taken'));
        }

        foreach ($reservedWords as $reservedWord) {
            if (strpos($username, $reservedWord) !== false) {
                $validationResult->setError(
                    'username',
                    __('Username cannot contain a reserved word ({word})', ['word' => $reservedWord])
                );
            }
        }
    }

    /**
     * Validate the email
     *
     * @param string           $email
     * @param int              $userId
     * @param ValidationResult $validationResult
     */
    private function validateEmailUpdate(
        string $email,
        int $userId,
        ValidationResult $validationResult
    ) {
        if (!is_email($email)) {
            $validationResult->setError('email', __('Email is not valid'));
        }
        if ($this->userRepository->existsEmail($email, $userId)) {
            // TODO Maybe inform the real user about the sign up attempt -> userdata key
            $validationResult->setError('email', __('Email is already registered for another user.'));
        }
    }

    /**
     * Validate the zip
     *
     * @param string           $street
     * @param ValidationResult $validationResult
     */
    public function validateStreet(string $street, ValidationResult $validationResult)
    {
        $this->validateLengthMin($street, 'street', $validationResult, 2);
        $this->validateLengthMax($street, 'street', $validationResult, 255);
    }

    /**
     * Validate the zip
     *
     * @param string           $zip
     * @param ValidationResult $validationResult
     */
    public function validateZip(string $zip, ValidationResult $validationResult)
    {
        $this->validateLengthMin($zip, 'zip', $validationResult, 3);
        $this->validateLengthMax($zip, 'zip', $validationResult, 10);
    }

    /**
     * Validate the city
     *
     * @param string           $city
     * @param ValidationResult $validationResult
     */
    public function validateCity(string $city, ValidationResult $validationResult)
    {
        $this->validateLengthMin($city, 'city', $validationResult, 2);
        $this->validateLengthMax($city, 'city', $validationResult, 255);
    }
}
