<?php

namespace App\Controller\User;

use App\Controller\AuthorizationInterface;
use App\Service\Auth\AuthorizationService;
use App\Service\Auth\JWT\JWTData;
use App\Service\Encoder\JSONEncoder;
use App\Service\ID\HashID;
use App\Service\User\UserService;
use App\Table\UserTable;
use App\Type\Auth\Role;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class UserViewAction
 */
class UserViewAction implements AuthorizationInterface
{
    public const NAME = 'api.v1.user.get';

    private UserService $user;
    private AuthorizationService $authorization;
    private JSONEncoder $json;

    /**
     * Constructor
     *
     * @param UserService          $user
     * @param AuthorizationService $authorization
     * @param JSONEncoder          $json
     */
    public function __construct(UserService $user, AuthorizationService $authorization, JSONEncoder $json)
    {
        $this->user = $user;
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
        $user = $this->user->getUser($userId);

        return $this->json->encode($response, [
            'message' => __('Found'),
            'success' => true,
            'user' => HashID::encodeRecord($user),
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
        $canRead = $this->authorization->hasRole($userId, Role::USERS_READ);
        $canWrite = $this->authorization->hasRole($userId, Role::USERS_WRITE);
        $canDelete = $this->authorization->hasRole($userId, Role::USERS_DELETE);

        return $isCreator || $isModifier || $canRead || $canWrite || $canDelete;
    }
}