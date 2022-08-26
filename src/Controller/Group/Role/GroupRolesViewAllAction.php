<?php

namespace App\Controller\Group\Role;

use App\Controller\AuthorizationInterface;
use App\Service\Auth\AuthorizationService;
use App\Service\Auth\GroupService;
use App\Service\Auth\JWT\JWTData;
use App\Service\Encoder\JSONEncoder;
use App\Service\ID\HashID;
use App\Type\Auth\Role;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class GroupRolesViewAllAction
 */
class GroupRolesViewAllAction implements AuthorizationInterface
{

    public const NAME = 'api.v1.user.group.all';

    private GroupService $group;
    private AuthorizationService $authorization;
    private JSONEncoder $json;

    /**
     * Constructor
     *
     * @param GroupService         $group
     * @param AuthorizationService $authorization
     * @param JSONEncoder          $json
     */
    public function __construct(GroupService $group, AuthorizationService $authorization, JSONEncoder $json)
    {
        $this->group = $group;
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
        $groupId = HashID::decodeSingle($args['group_hash']);
        $roles = $this->group->getRolesOfGroup($groupId);
        $count = count($roles);

        return $this->json->encode($response, [
            'message' => __('Found {count} roles', ['count' => $count]),
            'count' => $count,
            'success' => true,
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
        $userHash = JWTData::fromRequest($request)->get(JWTData::USER_HASH);
        $userId = HashID::decodeSingle($userHash);
        $canRead = $this->authorization->hasRole($userId, Role::GROUPS_READ);
        $canWrite = $this->authorization->hasRole($userId, Role::GROUPS_WRITE);
        $canCreate = $this->authorization->hasRole($userId, Role::GROUPS_CREATE);
        $canArchive = $this->authorization->hasRole($userId, Role::GROUPS_ARCHIVE);
        $canDelete = $this->authorization->hasRole($userId, Role::GROUPS_DELETE);

        return $canRead || $canWrite || $canCreate || $canArchive || $canDelete;
    }
}