<?php

namespace App\Service\Auth;

use App\Exception\ValidationException;
use App\Repository\GroupRepository;
use App\Repository\RoleRepository;
use App\Service\Validation\GroupValidation;
use App\Util\ValidationResult;

/**
 * Class GroupService
 */
class GroupService
{
    private GroupValidation $groupValidation;
    private GroupRepository $groupRepository;
    private RoleRepository $roleRepository;

    /**
     * Construct
     *
     * @param GroupValidation $groupValidation
     * @param GroupRepository $groupRepository
     * @param RoleRepository  $roleRepository
     */
    public function __construct(
        GroupValidation $groupValidation,
        GroupRepository $groupRepository,
        RoleRepository $roleRepository
    ) {
        $this->groupValidation = $groupValidation;
        $this->groupRepository = $groupRepository;
        $this->roleRepository = $roleRepository;
    }

    /**
     * Get a single group
     *
     * @param int $groupId
     *
     * @return array
     */
    public function getGroup(int $groupId): array
    {
        return $this->groupRepository->getGroup($groupId);
    }

    /**
     * Get all groups
     *
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array
     */
    public function getAllGroups(?int $limit = 1000, int $offset = null): array
    {
        return $this->groupRepository->getAllGroups($limit, $offset);
    }

    /**
     * Get all roles of a group
     *
     * @param int $groupId
     *
     * @return array
     */
    public function getRolesOfGroup(int $groupId): array
    {
        return $this->groupRepository->getRolesOfGroup($groupId);
    }

    /**
     * Create a group
     *
     * @param string   $name
     * @param string   $description
     * @param int|null $executorId
     *
     * @return int
     */
    public function createGroup(string $name, string $description, ?int $executorId = 0): int
    {
        $this->groupValidation->validateCreation($name, $description);

        return $this->groupRepository->createGroup($name, $description, $executorId);
    }

    /**
     * Assign role to group
     *
     * @param int      $groupId
     * @param int      $roleId
     * @param int|null $executorId
     *
     * @return bool
     */
    public function assignRoleToGroup(int $groupId, int $roleId, ?int $executorId = 0): bool
    {
        $this->roleRepository->getRole($roleId); // throws a 404 not found if role does not exist
        $hasRole = $this->groupRepository->hasRoleInGroup($groupId, $roleId);
        if ($hasRole) {
            $validationResult = new ValidationResult();
            $validationResult->setError('role', __('Role already assigned to group'));
            throw new ValidationException($validationResult);
        }

        return $this->groupRepository->assignRoleToGroup($groupId, $roleId, $executorId);
    }

    /**
     * Modify a group
     *
     * @param int         $groupId
     * @param string|null $name
     * @param string|null $description
     * @param int         $executorId
     *
     * @return bool
     */
    public function modifyGroup(int $groupId, int $executorId, ?string $name, ?string $description): bool
    {
        $this->groupValidation->validateModification($name, $description);

        return $this->groupRepository->updateGroup($groupId, $name, $description, $executorId);
    }

    /**
     * Remove a role from a group
     *
     * @param int      $groupId
     * @param int      $roleId
     * @param int|null $executorId
     *
     * @return bool
     */
    public function removeRoleFromGroup(int $groupId, int $roleId, ?int $executorId = 0): bool
    {
        $this->roleRepository->getRole($roleId); // throws a 404 not found if role does not exist
        $hasRole = $this->groupRepository->hasRoleInGroup($groupId, $roleId);
        if (!$hasRole) {
            $validationResult = new ValidationResult();
            $validationResult->setError('role', __('Role not assigned to group'));
            throw new ValidationException($validationResult);
        }

        return $this->groupRepository->removeRoleFromGroup($groupId, $roleId, $executorId);
    }

    /**
     * Archive a group
     *
     * @param int      $groupId
     * @param int|null $executorId
     *
     * @return bool
     */
    public function archive(int $groupId, ?int $executorId = 0): bool
    {
        return $this->groupRepository->archiveGroup($groupId, $executorId);
    }

    /**
     * Delete a group
     *
     * HANLDE WITH CARE!
     *
     * @param int $groupId
     *
     * @return bool
     */
    public function delete(int $groupId): bool
    {
        return $this->groupRepository->deleteGroup($groupId);
    }
}