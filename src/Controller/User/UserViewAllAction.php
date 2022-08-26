<?php

namespace App\Controller\User;

use App\Controller\AuthorizationInterface;
use App\Service\Auth\AuthorizationService;
use App\Service\Auth\JWT\JWTData;
use App\Service\Encoder\JSONEncoder;
use App\Service\ID\HashID;
use App\Service\User\UserService;
use App\Type\Auth\Role;
use App\Util\ArrayReader;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class UserViewAllAction
 */
class UserViewAllAction implements AuthorizationInterface
{
    public const NAME = 'api.v1.user.all';

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
     *
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): ResponseInterface {
        $queryParams = new ArrayReader($request->getQueryParams());
        $users = $this->user->getAllUsers($queryParams->findInt('limit'), $queryParams->findInt('limit'));
        $count = count($users);

        return $this->json->encode($response, [
            'message' => __('Found {count} users', ['count' => $count]),
            'success' => true,
            'count' => $count,
            'users' => HashID::encodeRecord($users),
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

        return $this->authorization->hasRole($userId, Role::USERS_READ);
    }
}