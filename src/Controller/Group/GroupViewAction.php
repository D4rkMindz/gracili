<?php

namespace App\Controller\Group;

use App\Controller\AuthorizationInterface;
use App\Service\Auth\AuthorizationService;
use App\Service\Auth\GroupService;
use App\Service\Auth\JWT\JWTData;
use App\Service\Encoder\JSONEncoder;
use App\Service\ID\HashID;
use App\Table\GroupTable;
use App\Type\Auth\Role;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class GroupViewAction
 */
class GroupViewAction implements AuthorizationInterface
{
    public const NAME = 'api.v1.group.get';

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
        $group = $this->group->getGroup($groupId);

        return $this->json->encode($response, [
            'message' => __('Found'),
            'success' => true,
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
        $groupHash = JWTData::fromRequest($request)->get(JWTData::USER_HASH);
        $groupId = HashID::decodeSingle($groupHash);
        $id = HashID::decodeSingle($args['group_hash']);
        $isCreator = $this->authorization->isCreator($groupId, GroupTable::getName(), $id);
        $isModifier = $this->authorization->isEditor($groupId, GroupTable::getName(), $id);
        $canRead = $this->authorization->hasRole($groupId, Role::GROUPS_READ);
        $canWrite = $this->authorization->hasRole($groupId, Role::GROUPS_WRITE);
        $canDelete = $this->authorization->hasRole($groupId, Role::GROUPS_DELETE);

        return $isCreator || $isModifier || $canRead || $canWrite || $canDelete;
    }
}