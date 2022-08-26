<?php

namespace App\Controller\User\Group;

use App\Controller\AuthorizationInterface;
use App\Service\Auth\AuthorizationService;
use App\Service\Auth\JWT\JWTData;
use App\Service\Encoder\JSONEncoder;
use App\Service\ID\HashID;
use App\Table\UserTable;
use App\Type\Auth\Role;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class UserGroupsViewAllAction
 */
class UserGroupsViewAllAction implements AuthorizationInterface
{

    public const NAME = 'api.v1.user.group.all';

    private AuthorizationService $authorization;
    private JSONEncoder $json;

    /**
     * Constructor
     *
     * @param AuthorizationService $authorization
     * @param JSONEncoder          $json
     */
    public function __construct(AuthorizationService $authorization, JSONEncoder $json)
    {
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
        $userId = HashID::decodeSingle($args['user_hash']);
        $groups = $this->authorization->getGroups($userId);
        $count = count($groups);

        return $this->json->encode($response, [
            'message' => __('Found {count} groups', ['count' => $count]),
            'count' => $count,
            'success' => true,
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
        $userHash = JWTData::fromRequest($request)->get(JWTData::USER_HASH);
        $userId = HashID::decodeSingle($userHash);
        $id = HashID::decodeSingle($args['user_hash']);
        $isCreator = $this->authorization->isCreator($userId, UserTable::getName(), $id);
        $isModifier = $this->authorization->isEditor($userId, UserTable::getName(), $id);
        $canRead = $this->authorization->hasRole($userId, Role::ROLES_READ)
            && $this->authorization->hasRole($userId, Role::USERS_READ);
        $canWrite = $this->authorization->hasRole($userId, Role::ROLES_WRITE)
            && $this->authorization->hasRole($userId, Role::USERS_WRITE);
        $canArchive = $this->authorization->hasRole($userId, Role::ROLES_ARCHIVE)
            && $this->authorization->hasRole($userId, Role::USERS_ARCHIVE);
        $canDelete = $this->authorization->hasRole($userId, Role::ROLES_DELETE)
            && $this->authorization->hasRole($userId, Role::USERS_DELETE);

        return $isCreator || $isModifier || $canRead || $canWrite || $canArchive || $canDelete;
    }
}