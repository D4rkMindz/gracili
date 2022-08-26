<?php

namespace App\Controller\Group;

use App\Controller\AuthorizationInterface;
use App\Service\Auth\AuthorizationService;
use App\Service\Auth\GroupService;
use App\Service\Auth\JWT\JWTData;
use App\Service\Encoder\JSONEncoder;
use App\Service\ID\HashID;
use App\Type\Auth\Role;
use App\Util\ArrayReader;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class GroupCreateAction
 */
class GroupCreateAction implements AuthorizationInterface
{
    public const NAME = 'api.v1.group.create';

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
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $executorHash = JWTData::fromRequest($request)->get(JWTData::USER_HASH);
        $executorId = HashID::decodeSingle($executorHash);

        $data = new ArrayReader($request->getParsedBody());
        $groupId = $this->group->createGroup(
            $data->findString('name', ''),
            $data->findString('description', ''),
            $executorId
        );

        $group = $this->group->getGroup($groupId);

        return $this->json->encode($response, [
            'success' => true,
            'message' => __('Created group successfully'),
            'group' => HashID::encodeRecord($group),
        ]);
    }

    /**
     * Authorize
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

        return $this->authorization->hasRole($groupId, Role::GROUPS_CREATE);
    }
}