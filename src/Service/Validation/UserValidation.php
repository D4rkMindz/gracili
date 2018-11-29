<?php

namespace App\Service\Validation;


use App\Repository\UserRepository;
use App\Util\ValidationResult;
use Interop\Container\Exception\ContainerException;
use Slim\Container;

/**
 * Class UserValidation
 */
class UserValidation extends AppValidation
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * UserValidation constructor.
     * @param Container $container
     * @throws ContainerException
     */
    public function __construct(Container $container)
    {
        $this->userRepository = $container->get(UserRepository::class);
    }

    /**
     * Validate the creation of a user.
     *
     * @param string $username
     * @param string $email
     * @param string $password
     * @return ValidationResult
     */
    public function validateCreate(?string $username, ?string $email, ?string $password): ValidationResult
    {
        $validationResult = new ValidationResult(__('Please check your data'));

        $this->validateUsername($username, $validationResult);
        $this->validateEmail($email, $validationResult);
        $this->validatePassword($password, $validationResult);

        return $validationResult;
    }

    /**
     * @param string $username
     * @param string $email
     * @param string $password
     * @return ValidationResult
     */
    public function validateUpdate(?string $username, ?string $email, ?string $password): ValidationResult
    {
        $validationResult = new ValidationResult(__('Please check your data'));

        if (!empty($username)) {
            $this->validateUsername($username, $validationResult);
        }

        if (!empty($email)) {
            $this->validateEmail($email, $validationResult);
        }

        if (!empty($password)) {
            $this->validatePassword($password, $validationResult);
        }

        return $validationResult;
    }

    /**
     * Validate the username.
     *
     * @param string $username
     * @param ValidationResult $validationResult
     */
    private function validateUsername(string $username, ValidationResult $validationResult)
    {
        $usernameExists = $this->userRepository->existsUsername($username);
        if ($usernameExists) {
            $validationResult->setError('username', __('Username already exists'));
        }
        $this->validateLengthMin($username, 'username', $validationResult, 5);
        $this->validateLengthMax($username, 'username', $validationResult, 30);
    }

    /**
     * Validate email.
     *
     * @param string $email
     * @param ValidationResult $validationResult
     */
    private function validateEmail(string $email, ValidationResult $validationResult)
    {
        if (!is_email($email)) {
            $validationResult->setError('email', __('Not a valid email'));
        }

        $emailExists = $this->userRepository->existsEmail($email);
        if ($emailExists) {
            $validationResult->setError('email', __('Email already registered'));
        }
    }

    /**
     * Validate password.
     *
     * @param string $password
     * @param ValidationResult $validationResult
     */
    private function validatePassword(string $password, ValidationResult $validationResult)
    {
        // TODO add custom Password validation rules
        $this->validateLengthMin($password, 'password', $validationResult, 6);
    }
}
