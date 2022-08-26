<?php

namespace App\Controller\User\Group;

use App\Controller\AuthorizationInterface;
use App\Service\Auth\AuthorizationService;
use App\Service\Auth\GroupService;
use App\Service\Auth\JWT\JWTData;
use App\Service\Encoder\JSONEncoder;
use App\Service\ID\HashID;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class UserGroupsRemoveAction
 */
class UserGroupsRemoveAction implements AuthorizationInterface
{
    public const NAME = 'api.v1.user.group.remove';

    private AuthorizationService $authorization;
    private GroupService $group;
    private JSONEncoder $json;

    /**
     * Constructor
     *
     * @param AuthorizationService $authorization
     * @param GroupService         $group
     * @param JSONEncoder          $json
     */
    public function __construct(AuthorizationService $authorization, GroupService $group, JSONEncoder $json)
    {
        $this->authorization = $authorization;
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

        $userId = HashID::decodeSingle($args['user_hash']);
        $groupId = HashID::decodeSingle($args['group_hash']);

        $group = $this->group->getGroup($groupId);
        $this->authorization->removeGroup($userId, $group['name'], $executorId);

        return $this->json->encode($response, [
            'success' => true,
            'message' => __('Successfully removed group from user'),
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