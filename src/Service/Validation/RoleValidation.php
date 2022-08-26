<?php

namespace App\Service\Validation;

use App\Repository\RoleRepository;
use App\Util\ValidationResult;

class RoleValidation extends AppValidation
{
    private RoleRepository $roleRepository;

    /**
     * Constructor
     *
     * @param RoleRepository $roleRepository
     */
    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * Validate the creation
     *
     * @param string $name
     * @param string $description
     *
     * @return void
     */
    public function validateCreation(string $name, string $description)
    {
        $validationResult = new ValidationResult();

        $this->validateName($name, $validationResult);
        $this->validateDescription($description, $validationResult);

        $this->throwOnError($validationResult);
    }

    /**
     * Validate the name of the role
     *
     * @param string           $name
     * @param ValidationResult $validationResult
     *
     * @return void
     */
    private function validateName(string $name, ValidationResult $validationResult)
    {
        $existsAlready = $this->roleRepository->existsRole($name);
        if ($existsAlready) {
            $validationResult->setError('name', __('Role already exists'));

            return;
        }

        if (!str_starts_with($name, 'role.')) {
            $validationResult->setError('name', __('Role name MUST start with role.'));
        }
        $this->validateLengthMax($name, 'name', $validationResult, 80);
        $this->validateLengthMin($name, 'name', $validationResult, 6);
    }

    /**
     * Validate the description of the role
     *
     * @param string           $description
     * @param ValidationResult $validationResult
     *
     * @return void
     */
    private function validateDescription(string $description, ValidationResult $validationResult)
    {
        $this->validateLengthMax($description, 'description', $validationResult, 10000);
        $this->validateLengthMin($description, 'description', $validationResult, 10);
    }

    /**
     * Validate the creation
     *
     * @param string|null $name
     * @param string|null $description
     *
     * @return void
     */
    public function validateModification(?string $description)
    {
        $validationResult = new ValidationResult();
        if (!empty($description)) {
            $this->validateDescription($description, $validationResult);
        }

        $this->throwOnError($validationResult);
    }
}