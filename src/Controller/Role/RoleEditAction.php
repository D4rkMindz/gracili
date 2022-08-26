<?php

namespace App\Controller\Role;

use App\Controller\AuthorizationInterface;
use App\Exception\RecordUpdateException;
use App\Service\Auth\AuthorizationService;
use App\Service\Auth\JWT\JWTData;
use App\Service\Auth\RoleService;
use App\Service\Encoder\JSONEncoder;
use App\Service\ID\HashID;
use App\Table\RoleTable;
use App\Type\Auth\Role;
use App\Util\ArrayReader;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RoleEditAction
 */
class RoleEditAction implements AuthorizationInterface
{
    public const NAME = 'api.v1.role.edit';

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
     *
     * @throws RecordUpdateException
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $jwtData = JWTData::fromRequest($request);
        $executorId = HashID::decodeSingle($jwtData->get(JWTData::USER_HASH));
        $roleId = HashID::decodeSingle($args['role_hash']);
        $data = new ArrayReader($request->getParsedBody());

        $updated = $this->role->modifyRole(
            $roleId,
            $executorId,
            $data->findString('description'),
        );

        if (!$updated) {
            throw new RecordUpdateException(__('Updating role failed'), 'id = ' . $roleId);
        }
        $role = $this->role->getRole($roleId);

        return $this->json->encode($response, [
            'message' => __('Updated role successfully'),
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
        $canWrite = $this->authorization->hasRole($roleId, Role::ROLES_WRITE);

        return $isCreator || $isModifier || $canWrite;
    }
}