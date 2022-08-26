<?php

namespace App\Repository;

use App\Exception\RecordNotFoundException;
use App\Exception\RecordUpdateException;
use App\Table\RoleTable;
use App\Table\UserHasRoleTable;
use InvalidArgumentException;

/**
 * Class RoleRepository
 */
class RoleRepository extends AppRepository
{
    private RoleTable $roleTable;
    private UserHasRoleTable $userHasRoleTable;

    /**
     * @param RoleTable        $roleTable
     * @param UserHasRoleTable $userHasRoleTable
     */
    public function __construct(RoleTable $roleTable, UserHasRoleTable $userHasRoleTable)
    {
        $this->roleTable = $roleTable;
        $this->userHasRoleTable = $userHasRoleTable;
    }

    /**
     * Check if a user has a specific role
     *
     * The role could be assigned directly (user_has_role) or via a group (group_has_role)
     *
     * @param int    $userId
     * @param string $role
     *
     * @return bool
     */
    public function hasRole(int $userId, string $role, ?bool $onlyDirectlyAssigned = false): bool
    {
        $query = $this->roleTable->newSelect();
        $query->select([1])
            ->join([
                'user_has_role' => [
                    'table' => 'user_has_role',
                    'type' => 'INNER',
                    'conditions' => 'user_has_role.role_id = role.id',
                ],
            ])
            ->where([
                'user_has_role.user_id' => $userId,
                'role.name' => $role,
            ]);

        $hasRole = $query->execute()->count() > 0;
        if ($hasRole) {
            return true;
        }

        if ($onlyDirectlyAssigned) {
            return false;
        }

        $query = $this->roleTable->newSelect();
        $query->select([1])
            ->join([
                'group_has_role' => [
                    'table' => 'group_has_role',
                    'type' => 'INNER',
                    'conditions' => 'group_has_role.role_id = role.id',
                ],
                'group' => [
                    'table' => 'group',
                    'type' => 'INNER',
                    'conditions' => 'group.id = group_has_role.group_id',
                ],
                'user_has_group' => [
                    'table' => 'user_has_group',
                    'type' => 'INNER',
                    'conditions' => 'user_has_group.group_id = group.id',
                ],
            ])
            ->where([
                'user_has_group.user_id' => $userId,
                'role.name' => $role,
            ]);

        return $query->execute()->count() > 0;
    }

    /**
     * Get the roles that have been assigned to a user
     *
     * @param int $userId
     *
     * @return array
     */
    public function findAssignedRoles(int $userId): array
    {
        $query = $this->roleTable->newSelect();
        $query->select([
            'role.id',
            'role.name',
            'role.description',
        ])
            ->join([
                'user_has_role' => [
                    'table' => 'user_has_role',
                    'type' => 'INNER',
                    'conditions' => 'user_has_role.role_id = role.id',
                ],
            ])
            ->where([
                'user_has_role.user_id' => $userId,
            ]);
        $result = $query->execute()->fetchAll('assoc');

        if (!empty($result)) {
            return $result;
        }

        return [];
    }

    /**
     * Get the roles that have been assigned to a user
     *
     * @param int $userId
     *
     * @return array
     */
    public function findIndirectlyAssignedRoles(int $userId): array
    {
        $query = $this->roleTable->newSelect();
        $query->select([
            'role.id',
            'role.name',
            'role.description',
        ])
            ->join([
                'group_has_role' => [
                    'table' => 'group_has_role',
                    'type' => 'INNER',
                    'conditions' => 'group_has_role.role_id = role.id',
                ],
                'group' => [
                    'table' => 'group',
                    'type' => 'INNER',
                    'conditions' => 'group.id = group_has_role.group_id',
                ],
                'user_has_group' => [
                    'table' => 'user_has_group',
                    'type' => 'INNER',
                    'conditions' => 'user_has_group.group_id = group.id',
                ],
            ])
            ->where([
                'user_has_group.user_id' => $userId,
            ]);
        $result = $query->execute()->fetchAll('assoc');

        if (!empty($result)) {
            return $result;
        }

        return [];
    }

    /**
     * Check if a role exists
     *
     * @param string $roleName
     *
     * @return bool
     */
    public function existsRole(string $roleName): bool
    {
        return $this->roleTable->exist(['name' => $roleName]);
    }

    /**
     * Get a single role
     *
     * @param int $roleId
     *
     * @return array
     * @throws RecordNotFoundException
     */
    public function getRole(int $roleId): array
    {
        $query = $this->roleTable->newSelect();
        $query->select(['id', 'name', 'description'])
            ->where(['id' => $roleId]);

        $result = $query->execute()->fetch('assoc');

        if (!empty($result)) {
            return $result;
        }

        throw new RecordNotFoundException(__('Role not found'), 'role_id = ' . $roleId);
    }

    /**
     * Get all roles
     *
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array
     * @throws RecordNotFoundException
     */
    public function getAllRoles(?int $limit = 1000, int $offset = null): array
    {
        $query = $this->roleTable->newSelect();
        $query->select(['id', 'name', 'description']);
        if ($limit !== null) {
            $query->limit($limit);
        }

        if ($offset !== null) {
            $query->offset($offset);
        }

        $result = $query->execute()->fetchAll('assoc');

        if (!empty($result)) {
            return $result;
        }

        throw new RecordNotFoundException(__('No role found'), 'count = ' . $limit . ' offset = ' . $offset);
    }

    /**
     * Assign role to a user
     *
     * @param int      $userId
     * @param string   $role
     * @param int|null $executorId
     *
     * @return bool
     */
    public function assignRole(int $userId, string $role, ?int $executorId = 0): bool
    {
        $roleId = $this->getRoleIdByName($role);
        $userHasGroup = [
            'user_id' => $userId,
            'role_id' => $roleId,
        ];

        $this->userHasRoleTable->insert($userHasGroup, $executorId);

        return true;
    }

    /**
     * Get the role id by its name
     *
     * @param string $role
     *
     * @return int
     */
    private function getRoleIdByName(string $role): int
    {
        $query = $this->roleTable->newSelect();
        $query->select(['id'])->where(['name' => $role]);
        $result = $query->execute()->fetch('assoc');
        if (empty($result)) {
            throw new InvalidArgumentException(__('Role cannot be assigned'));
        }

        return $result['id'];
    }

    /**
     * Remove all assignments of a role to a user
     *
     * @param int      $userId
     * @param string   $role
     * @param int|null $executorId
     *
     * @return bool
     * @throws RecordUpdateException
     */
    public function removeRole(int $userId, string $role, ?int $executorId = 0): bool
    {
        $roleId = $this->getRoleIdByName($role);
        $query = $this->userHasRoleTable->newSelect();
        $query->select(['id'])
            ->where(['user_id' => $userId, 'role_id' => $roleId]);
        $result = $query->execute()->fetch('assoc');
        if (empty($result)) {
            throw new RecordUpdateException(__('User has role not assigned'));
        }

        return $this->userHasRoleTable->archive($result['id'], $executorId);
    }

    /**
     * Remove all assignments of a role to a user
     *
     * @param int      $userId
     * @param int|null $executorId
     *
     * @return bool
     */
    public function removeAllRoles(int $userId, ?int $executorId = 0): bool
    {
        return $this->userHasRoleTable->archiveAll(['user_id' => $userId], $executorId);
    }

    /**
     * Delete all assignments of a role to a group
     *
     * @param int $userId
     *
     * @return bool
     */
    public function deleteAllRoles(int $userId): bool
    {
        return $this->userHasRoleTable->deleteAll(['user_id' => $userId]);
    }

    /**
     * Update a role
     *
     * @param int         $roleId
     * @param string|null $description
     * @param int|null    $executorId
     *
     * @return bool
     */
    public function updateRole(int $roleId, ?string $description, ?int $executorId = 0): bool
    {
        $row = [];
        if (!empty($description)) {
            $row['description'] = $description;
        }
        if (!empty($row)) {
            return $this->roleTable->update($row, ['id' => $roleId], $executorId);
        }

        return false;
    }
}