<?php

namespace App\Service\Validation;

use App\Repository\GroupRepository;
use App\Util\ValidationResult;

class GroupValidation extends AppValidation
{
    private GroupRepository $groupRepository;

    /**
     * Constructor
     *
     * @param GroupRepository $groupRepository
     */
    public function __construct(GroupRepository $groupRepository)
    {
        $this->groupRepository = $groupRepository;
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
     * Validate the name of the group
     *
     * @param string           $name
     * @param ValidationResult $validationResult
     *
     * @return void
     */
    private function validateName(string $name, ValidationResult $validationResult)
    {
        $existsAlready = $this->groupRepository->existsGroup($name);
        if ($existsAlready) {
            $validationResult->setError('name', __('Group already exists'));

            return;
        }

        if (!str_starts_with($name, 'group.')) {
            $validationResult->setError('name', __('Group name MUST start with group.'));
        }
        $this->validateLengthMax($name, 'name', $validationResult, 80);
        $this->validateLengthMin($name, 'name', $validationResult, 6);
    }

    /**
     * Validate the description of the group
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
    public function validateModification(?string $name, ?string $description)
    {
        $validationResult = new ValidationResult();

        if (!empty($name)) {
            $this->validateName($name, $validationResult);
        }
        if (!empty($description)) {
            $this->validateDescription($description, $validationResult);
        }

        $this->throwOnError($validationResult);
    }
}