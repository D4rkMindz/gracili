<?php

namespace App\Repository;

use App\Table\GroupTable;
use App\Table\UserHasGroupTable;
use InvalidArgumentException;

class GroupRepository extends AppRepository
{
    private GroupTable $groupTable;
    private UserHasGroupTable $userHasGroupTable;

    /**
     * @param GroupTable        $groupTable
     * @param UserHasGroupTable $userHasGroupTable
     */
    public function __construct(GroupTable $groupTable, UserHasGroupTable $userHasGroupTable)
    {
        $this->groupTable = $groupTable;
        $this->userHasGroupTable = $userHasGroupTable;
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
            'group.description'
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

    public function assignGroup(int $userId, string $group)
    {
        $query = $this->groupTable->newSelect();
        $query->select(['id'])->where(['name' => $group]);
        $result = $query->execute()->fetch('assoc');
        if (empty($result)) {
            throw new InvalidArgumentException(__('Group cannot be assigned'));
        }

        $userHasGroup = [
            'user_id' => $userId,
            'group_id' => $result['id'],
        ];

        $this->userHasGroupTable->insert($userHasGroup);
    }
}