<?php

namespace App\Service\Auth;

use App\Repository\GroupRepository;
use App\Repository\RoleRepository;

/**
 * Class AuthorizationService
 */
class AuthorizationService
{
    private GroupRepository $groupRepository;
    private RoleRepository $roleRepository;

    /**
     * Constructor
     *
     * @param GroupRepository $groupRepository
     * @param RoleRepository  $roleRepository
     */
    public function __construct(GroupRepository $groupRepository, RoleRepository $roleRepository)
    {
        $this->groupRepository = $groupRepository;
        $this->roleRepository = $roleRepository;
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
}