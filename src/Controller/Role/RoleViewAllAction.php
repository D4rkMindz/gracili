<?php

namespace App\Controller\Role;

use App\Controller\AuthorizationInterface;
use App\Service\Auth\AuthorizationService;
use App\Service\Auth\JWT\JWTData;
use App\Service\Auth\RoleService;
use App\Service\Encoder\JSONEncoder;
use App\Service\ID\HashID;
use App\Type\Auth\Role;
use App\Util\ArrayReader;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RoleViewAllAction
 */
class RoleViewAllAction implements AuthorizationInterface
{
    public const NAME = 'api.v1.role.all';

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
     *
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): ResponseInterface {
        $queryParams = new ArrayReader($request->getQueryParams());
        $roles = $this->role->getAllRoles($queryParams->findInt('limit'), $queryParams->findInt('limit'));
        $count = count($roles);

        return $this->json->encode($response, [
            'message' => __('Found {count} roles', ['count' => $count]),
            'success' => true,
            'count' => $count,
            'roles' => HashID::encodeRecord($roles),
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

        return $this->authorization->hasRole($roleId, Role::ROLES_READ);
    }
}