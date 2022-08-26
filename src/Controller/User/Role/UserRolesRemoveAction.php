<?php

namespace App\Controller\User\Role;

use App\Controller\AuthorizationInterface;
use App\Service\Auth\AuthorizationService;
use App\Service\Auth\JWT\JWTData;
use App\Service\Auth\RoleService;
use App\Service\Encoder\JSONEncoder;
use App\Service\ID\HashID;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class UserRolesRemoveAction
 */
class UserRolesRemoveAction implements AuthorizationInterface
{
    public const NAME = 'api.v1.user.role.remove';

    private AuthorizationService $authorization;
    private RoleService $role;
    private JSONEncoder $json;

    /**
     * Constructor
     *
     * @param AuthorizationService $authorization
     * @param RoleService          $role
     * @param JSONEncoder          $json
     */
    public function __construct(AuthorizationService $authorization, RoleService $role, JSONEncoder $json)
    {
        $this->authorization = $authorization;
        $this->role = $role;
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
        $jwtData = JWTData::fromRequest($request);
        $executorId = HashID::decodeSingle($jwtData->get(JWTData::USER_HASH));

        $userId = HashID::decodeSingle($args['user_hash']);
        $roleId = HashID::decodeSingle($args['role_hash']);

        $role = $this->role->getRole($roleId);
        $this->authorization->removeRole($userId, $role['name'], $executorId);

        return $this->json->encode($response, [
            'success' => true,
            'message' => __('Successfully removed role from user'),
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
        return false; // it's a security admin thing
    }
}