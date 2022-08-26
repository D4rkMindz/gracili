<?php

namespace App\Controller\Group\Role;

use App\Controller\AuthorizationInterface;
use App\Service\Auth\GroupService;
use App\Service\Auth\JWT\JWTData;
use App\Service\Encoder\JSONEncoder;
use App\Service\ID\HashID;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class GroupRoleAddAction
 */
class GroupRoleAddAction implements AuthorizationInterface
{
    public const NAME = 'api.v1.group.role.add';

    private GroupService $group;
    private JSONEncoder $json;

    /**
     * Constructor
     *
     * @param GroupService $group
     * @param JSONEncoder  $json
     */
    public function __construct(GroupService $group, JSONEncoder $json)
    {
        $this->group = $group;
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

        $groupId = HashID::decodeSingle($args['group_hash']);
        $roleId = HashID::decodeSingle($args['role_hash']);

        $group = $this->group->getGroup($groupId);
        $this->group->assignRoleToGroup($groupId, $roleId, $executorId);

        return $this->json->encode($response, [
            'success' => true,
            'message' => __('Successfully assigned role to group'),
            'group' => HashID::encodeRecord($group),
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