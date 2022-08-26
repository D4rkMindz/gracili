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
 * Class GroupViewAllAction
 */
class GroupViewAllAction implements AuthorizationInterface
{
    public const NAME = 'api.v1.group.all';

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
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): ResponseInterface {
        $queryParams = new ArrayReader($request->getQueryParams());
        $groups = $this->group->getAllGroups($queryParams->findInt('limit'), $queryParams->findInt('limit'));
        $count = count($groups);

        return $this->json->encode($response, [
            'message' => __('Found {count} groups', ['count' => $count]),
            'success' => true,
            'count' => $count,
            'groups' => HashID::encodeRecord($groups),
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

        return $this->authorization->hasRole($groupId, Role::GROUPS_READ);
    }
}