<?php

namespace App\Repository;

use App\Table\RoleTable;
use App\Table\UserHasRoleTable;

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
    public function hasRole(int $userId, string $role): bool
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
            'role.description'
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
}