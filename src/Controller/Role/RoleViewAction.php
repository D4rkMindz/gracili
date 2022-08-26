<?php

namespace App\Controller\Role;

use App\Controller\AuthorizationInterface;
use App\Service\Auth\AuthorizationService;
use App\Service\Auth\JWT\JWTData;
use App\Service\Auth\RoleService;
use App\Service\Encoder\JSONEncoder;
use App\Service\ID\HashID;
use App\Table\RoleTable;
use App\Type\Auth\Role;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RoleViewAction
 */
class RoleViewAction implements AuthorizationInterface
{
    public const NAME = 'api.v1.role.get';

    private RoleService $role;
    private AuthorizationService $authorization;
    private JSONEncoder $json;

    /**
     * Constructor
     *
     * @param RoleService          $role
     * @param AuthorizationService $authorization
     * @param JSONEncoder          $json
     */
    public function __construct(RoleService $role, AuthorizationService $authorization, JSONEncoder $json)
    {
        $this->role = $role;
        $this->authorization = $authorization;
        $this->json = $json;
    }

    /**
     * Invoke
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param array                  $args
     *
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $roleId = HashID::decodeSingle($args['role_hash']);
        $role = $this->role->getRole($roleId);

        return $this->json->encode($response, [
            'message' => __('Found'),
            'success' => true,
            'role' => HashID::encodeRecord($role),
        ]);
    }

    /**
     * Authorization
     *
     * @param ServerRequestInterface $request
     * @param array                  $args
     *
     * @return bool
     */
    public function authorize(ServerRequestInterface $request, array $args): bool
    {
        $roleHash = JWTData::fromRequest($request)->get(JWTData::USER_HASH);
        $roleId = HashID::decodeSingle($roleHash);
        $id = HashID::decodeSingle($args['role_hash']);
        $isCreator = $this->authorization->isCreator($roleId, RoleTable::getName(), $id);
        $isModifier = $this->authorization->isEditor($roleId, RoleTable::getName(), $id);
        $canRead = $this->authorization->hasRole($roleId, Role::ROLES_READ);
        $canWrite = $this->authorization->hasRole($roleId, Role::ROLES_WRITE);
        $canDelete = $this->authorization->hasRole($roleId, Role::ROLES_DELETE);

        return $isCreator || $isModifier || $canRead || $canWrite || $canDelete;
    }
}