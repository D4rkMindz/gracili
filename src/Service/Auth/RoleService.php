<?php

namespace App\Service\Auth;

use App\Repository\RoleRepository;
use App\Service\Validation\RoleValidation;

/**
 * Class RoleService
 */
class RoleService
{
    private RoleValidation $roleValidation;
    private RoleRepository $roleRepository;

    /**
     * Construct
     *
     * @param RoleValidation $roleValidation
     * @param RoleRepository $roleRepository
     */
    public function __construct(RoleValidation $roleValidation, RoleRepository $roleRepository)
    {
        $this->roleValidation = $roleValidation;
        $this->roleRepository = $roleRepository;
    }

    /**
     * Get a single role
     *
     * @param int $roleId
     *
     * @return array
     */
    public function getRole(int $roleId): array
    {
        return $this->roleRepository->getRole($roleId);
    }

    /**
     * Get all roles
     *
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array
     */
    public function getAllRoles(?int $limit = 1000, int $offset = null): array
    {
        return $this->roleRepository->getAllRoles($limit, $offset);
    }

    /**
     * Modify a role
     *
     * @param int         $roleId
     * @param int         $executorId
     *
     * @param string|null $description
     *
     * @return bool
     */
    public function modifyRole(int $roleId, int $executorId, ?string $description): bool
    {
        $this->roleValidation->validateModification($description);

        return $this->roleRepository->updateRole($roleId, $description, $executorId);
    }
}