<?php

namespace App\Service\Auth;

use App\Exception\ValidationException;
use App\Repository\GroupRepository;
use App\Repository\RoleRepository;
use App\Util\ValidationResult;
use Cake\Database\Connection;

/**
 * Class AuthorizationService
 */
class AuthorizationService
{
    private GroupRepository $groupRepository;
    private RoleRepository $roleRepository;
    private Connection $connection;

    /**
     * Constructor
     *
     * @param GroupRepository $groupRepository
     * @param RoleRepository  $roleRepository
     * @param Connection      $connection
     */
    public function __construct(
        GroupRepository $groupRepository,
        RoleRepository $roleRepository,
        Connection $connection
    ) {
        $this->groupRepository = $groupRepository;
        $this->roleRepository = $roleRepository;
        $this->connection = $connection;
    }

    /**
     * Get all roles of a user
     *
     * @param int $userId
     *
     * @return array
     */
    public function getRoles(int $userId): array
    {
        return $this->roleRepository->findAssignedRoles($userId);
    }

    /**
     * Get roles assigned through groups
     *
     * @param int $userId
     *
     * @return array
     */
    public function getIndirectlyAssignedRoles(int $userId): array
    {
        return $this->roleRepository->findIndirectlyAssignedRoles($userId);
    }

    /**
     * Assign a role to a user
     *
     * @param int      $userId
     * @param string   $role
     * @param int|null $executorId
     *
     * @return bool
     */
    public function assignRole(int $userId, string $role, ?int $executorId = 0): bool
    {
        $hasRole = $this->roleRepository->hasRole($userId, $role, true);
        if ($hasRole) {
            $validationResult = new ValidationResult();
            $validationResult->setError('role', __('Role already assigned'));
            throw new ValidationException($validationResult);
        }

        return $this->roleRepository->assignRole($userId, $role, $executorId);
    }

    /**
     * Check if a user has a role assigned
     *
     * @param int    $userId
     * @param string $role
     *
     * @return bool
     */
    public function hasRole(int $userId, string $role): bool
    {
        return $this->roleRepository->hasRole($userId, $role);
    }

    /**
     * Remove a role from a user
     *
     * @param int      $userId
     * @param string   $role
     * @param int|null $executorId
     *
     * @return bool
     */
    public function removeRole(int $userId, string $role, ?int $executorId = 0): bool
    {
        $hasRole = $this->roleRepository->hasRole($userId, $role, true);
        if (!$hasRole) {
            $validationResult = new ValidationResult();
            $validationResult->setError('role', __('Role not assigned to user'));
            throw new ValidationException($validationResult);
        }

        return $this->roleRepository->removeRole($userId, $role, $executorId);
    }

    /**
     * Get all groups of a user
     *
     * @param int $userId
     *
     * @return array
     */
    public function getGroups(int $userId): array
    {
        return $this->groupRepository->findAssignedGroups($userId);
    }

    /**
     * Assign a group to a user
     *
     * @param int      $userId
     * @param string   $group
     * @param int|null $executorId
     *
     * @return bool
     * @throws ValidationException
     */
    public function assignGroup(int $userId, string $group, ?int $executorId = 0): bool
    {
        $hasGroup = $this->groupRepository->hasGroup($userId, $group);
        if ($hasGroup) {
            $validationResult = new ValidationResult();
            $validationResult->setError('group', __('Group already assigned'));
            throw new ValidationException($validationResult);
        }

        return $this->groupRepository->assignGroup($userId, $group, $executorId);
    }

    /**
     * Check if a user has a group assigned
     *
     * @param int    $userId
     * @param string $group
     *
     * @return bool
     */
    public function hasGroup(int $userId, string $group): bool
    {
        return $this->groupRepository->hasGroup($userId, $group);
    }

    /**
     * Remove a group from a user
     *
     * @param int      $userId
     * @param string   $group
     * @param int|null $executorId
     *
     * @return bool
     */
    public function removeGroup(int $userId, string $group, ?int $executorId = 0): bool
    {
        $hasGroup = $this->groupRepository->hasGroup($userId, $group);
        if (!$hasGroup) {
            $validationResult = new ValidationResult();
            $validationResult->setError('group', __('Group not assigned to user'));
            throw new ValidationException($validationResult);
        }

        return $this->groupRepository->removeGroup($userId, $group, $executorId);
    }

    /**
     * Check if the user is creator of a record
     *
     * @param int    $userId
     * @param string $table
     * @param int    $id
     *
     * @return bool
     */
    public function isCreator(int $userId, string $table, int $id)
    {
        $query = $this->connection->newQuery()
            ->select(['created_by'])
            ->from($table)
            ->where(['id' => $id]);
        $result = $query->execute()->fetch('assoc');

        if (!isset($result['created_by'])) {
            return false;
        }

        return $userId === (int)$result['created_by'];
    }

    /**
     * Check if a user has edited any record
     *
     * @param int    $userId
     * @param string $table
     * @param int    $id
     *
     * @return bool
     */
    public function isEditor(int $userId, string $table, int $id)
    {
        $query = $this->connection->newQuery()
            ->select(['modified_by'])
            ->from($table)
            ->where(['id' => $id]);
        $result = $query->execute()->fetch('assoc');

        if (!isset($result['modified_by'])) {
            return false;
        }

        return $userId === (int)$result['modified_by'];
    }
}