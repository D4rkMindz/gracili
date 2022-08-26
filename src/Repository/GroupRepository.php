<?php

namespace App\Repository;

use App\Exception\RecordNotFoundException;
use App\Exception\RecordUpdateException;
use App\Table\GroupHasRoleTable;
use App\Table\GroupTable;
use App\Table\RoleTable;
use App\Table\UserHasGroupTable;
use InvalidArgumentException;

class GroupRepository extends AppRepository
{
    private GroupTable $groupTable;
    private UserHasGroupTable $userHasGroupTable;
    private GroupHasRoleTable $groupHasRoleTable;

    /**
     * Constructor
     *
     * @param GroupTable        $groupTable
     * @param UserHasGroupTable $userHasGroupTable
     * @param GroupHasRoleTable $groupHasRoleTable
     */
    public function __construct(
        GroupTable $groupTable,
        UserHasGroupTable $userHasGroupTable,
        GroupHasRoleTable $groupHasRoleTable
    ) {
        $this->groupTable = $groupTable;
        $this->userHasGroupTable = $userHasGroupTable;
        $this->groupHasRoleTable = $groupHasRoleTable;
    }

    /**
     * Check if a user has a specific group
     *
     * @param int    $userId
     * @param string $group
     *
     * @return bool
     */
    public function hasGroup(int $userId, string $group): bool
    {
        $query = $this->groupTable->newSelect();
        $query->select([1])
            ->join([
                'user_has_group' => [
                    'table' => 'user_has_group',
                    'type' => 'INNER',
                    'conditions' => 'user_has_group.group_id = group.id',
                ],
            ])
            ->where([
                'user_has_group.user_id' => $userId,
                'group.name' => $group,
            ]);

        return $query->execute()->count() > 0;
    }

    /**
     * Check if a role is contained in the group
     *
     * @param int $groupId
     * @param int $roleId
     *
     * @return bool
     */
    public function hasRoleInGroup(int $groupId, int $roleId): bool
    {
        return $this->groupHasRoleTable->exist([
            'group_id' => $groupId,
            'role_id' => $roleId,
        ]);
    }

    /**
     * Get the groups that have been assigned to a user
     *
     * @param int $userId
     *
     * @return array
     */
    public function findAssignedGroups(int $userId): array
    {
        $query = $this->groupTable->newSelect();
        $query->select([
            'group.id',
            'group.name',
            'group.description',
        ])
            ->join([
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
     * Check if a group exists
     *
     * @param string $groupName
     *
     * @return bool
     */
    public function existsGroup(string $groupName): bool
    {
        return $this->groupTable->exist(['name' => $groupName]);
    }

    /**
     * Get a single group
     *
     * @param int $groupId
     *
     * @return array
     * @throws RecordNotFoundException
     */
    public function getGroup(int $groupId): array
    {
        $query = $this->groupTable->newSelect();
        $query->select(['id', 'name', 'description'])
            ->where(['id' => $groupId]);

        $result = $query->execute()->fetch('assoc');

        if (!empty($result)) {
            return $result;
        }

        throw new RecordNotFoundException(__('Group not found'), 'group_id = ' . $groupId);
    }

    /**
     * Get all groups
     *
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array
     * @throws RecordNotFoundException
     */
    public function getAllGroups(?int $limit = 1000, int $offset = null): array
    {
        $query = $this->groupTable->newSelect();
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

        throw new RecordNotFoundException(__('No group found'), 'count = ' . $limit . ' offset = ' . $offset);
    }

    /**
     * Get all roles of a group
     *
     * @param int $groupId
     *
     * @return array
     * @throws RecordNotFoundException
     */
    public function getRolesOfGroup(int $groupId): array
    {
        $query = $this->groupHasRoleTable->newSelect();
        $query->select([
            RoleTable::getName() . '.id',
            RoleTable::getName() . '.name',
            RoleTable::getName() . '.description',
        ])
            ->join([
                RoleTable::getName() => [
                    'table' => RoleTable::getName(),
                    'type' => 'INNER',
                    'conditions' => sprintf('%s.role_id = %s.id', GroupHasRoleTable::getName(), RoleTable::getName()),
                ],
            ])
            ->where([GroupHasRoleTable::getName() . '.group_id' => $groupId]);

        $result = $query->execute()->fetchAll('assoc');

        if (!empty($result)) {
            return $result;
        }

        throw new RecordNotFoundException(__('No role assigned to group found'), 'group = ' . $groupId);
    }

    /**
     * Assign a role to a group
     *
     * @param int      $groupId
     * @param int      $roleId
     * @param int|null $executorId
     *
     * @return bool
     */
    public function assignRoleToGroup(int $groupId, int $roleId, ?int $executorId = 0): bool
    {
        $this->groupHasRoleTable->insert([
            'group_id' => $groupId,
            'role_id' => $roleId,
        ], $executorId);

        return true;
    }

    /**
     * Remove a role from a group
     *
     * @param int      $groupId
     * @param int      $roleId
     * @param int|null $executorId
     *
     * @return bool
     * @throws RecordNotFoundException
     */
    public function removeRoleFromGroup(int $groupId, int $roleId, ?int $executorId = 0): bool
    {
        $query = $this->groupHasRoleTable->newSelect();
        $query->select(['id'])->where(['group_id' => $groupId, 'role_id' => $roleId]);
        $result = $query->execute()->fetch('assoc');

        if (empty($result)) {
            throw new RecordNotFoundException(
                __('Role is not assigned to group'),
                'group_id = ' . $groupId . ' role_id = ' . $roleId
            );
        }

        $this->groupHasRoleTable->archive($result['id'], $executorId);

        return true;
    }

    /**
     * Assign a group to a user
     *
     * @param int      $userId
     * @param string   $group
     * @param int|null $executorId
     *
     * @return bool
     */
    public function assignGroup(int $userId, string $group, ?int $executorId = 0): bool
    {
        $groupId = $this->getGroupIdByName($group);

        $userHasGroup = [
            'user_id' => $userId,
            'group_id' => $groupId,
        ];

        $this->userHasGroupTable->insert($userHasGroup, $executorId);

        return true;
    }

    /**
     * Get group id by name
     *
     * @param string $group
     *
     * @return int
     */
    private function getGroupIdByName(string $group): int
    {
        $query = $this->groupTable->newSelect();
        $query->select(['id'])->where(['name' => $group]);
        $result = $query->execute()->fetch('assoc');
        if (empty($result)) {
            throw new InvalidArgumentException(__('Group cannot be assigned'));
        }

        return $result['id'];
    }

    /**
     * Remove the group from a user
     *
     * @param int      $userId
     * @param string   $group
     * @param int|null $executorId
     *
     * @return bool
     * @throws RecordUpdateException
     */
    public function removeGroup(int $userId, string $group, ?int $executorId = 0): bool
    {
        $groupId = $this->getGroupIdByName($group);
        $query = $this->userHasGroupTable->newSelect();
        $query->select(['id'])
            ->where(['user_id' => $userId, 'group_id' => $groupId]);
        $result = $query->execute()->fetch('assoc');
        if (empty($result)) {
            throw new RecordUpdateException(__('User has group not assigned'));
        }

        return $this->userHasGroupTable->archive($result['id'], $executorId);
    }

    /**
     * Remove all assignments of a group to a user
     *
     * @param int      $userId
     * @param int|null $executorId
     *
     * @return bool
     */
    public function removeAllGroups(int $userId, ?int $executorId = 0): bool
    {
        return $this->userHasGroupTable->archiveAll(['user_id' => $userId], $executorId);
    }

    /**
     * Delete all assignments of a user to a group
     *
     * @param int $userId
     *
     * @return bool
     */
    public function deleteAllGroups(int $userId): bool
    {
        return $this->userHasGroupTable->deleteAll(['user_id' => $userId]);
    }

    /**
     * Create a user group
     *
     * @param string   $name
     * @param string   $description
     * @param int|null $executorId
     *
     * @return int
     */
    public function createGroup(string $name, string $description, ?int $executorId = 0): int
    {
        $row = [
            'name' => $name,
            'description' => $description,
        ];

        return $this->groupTable->insert($row, $executorId)->lastInsertId();
    }

    /**
     * Update a group
     *
     * @param int         $groupId
     * @param string|null $name
     * @param string|null $description
     * @param int|null    $executorId
     *
     * @return bool
     */
    public function updateGroup(int $groupId, ?string $name, ?string $description, ?int $executorId = 0): bool
    {
        $row = [];
        if (!empty($name)) {
            $row['name'] = $name;
        }
        if (!empty($description)) {
            $row['description'] = $description;
        }
        if (!empty($row)) {
            return $this->groupTable->update($row, ['id' => $groupId], $executorId);
        }

        return false;
    }

    /**
     * Archive a group
     *
     * @param int      $groupId
     * @param int|null $executorId
     *
     * @return bool
     */
    public function archiveGroup(int $groupId, ?int $executorId = 0): bool
    {
        $this->userHasGroupTable->archiveAll(['group_id' => $groupId], $executorId);

        return $this->groupTable->archive($groupId, $executorId);
    }

    /**
     * Delete a group
     *
     * HANDLE WITH CARE!
     *
     * @param int $groupId
     *
     * @return bool
     */
    public function deleteGroup(int $groupId): bool
    {
        $this->userHasGroupTable->deleteAll(['group_id' => $groupId]);

        return $this->groupTable->delete($groupId);
    }
}