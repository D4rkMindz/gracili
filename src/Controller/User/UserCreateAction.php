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
 * Class UserCreateAction
 */
class UserCreateAction implements AuthorizationInterface
{
    public const NAME = 'api.v1.user.create';

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
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $jwtData = JWTData::fromRequest($request);
        $executorId = HashID::decodeSingle($jwtData->get(JWTData::USER_HASH));
        $data = new ArrayReader($request->getParsedBody());
        $userId = $this->user->createUser(
            $data->findString('username', ''),
            $data->findString('email', ''),
            $data->findString('password', ''),
            $data->findString('first_name', ''),
            $data->findString('last_name'),
            $data->findString('language'),
            $executorId
        );

        $user = $this->user->getUser($userId);

        return $this->json->encode($response, [
            'success' => true,
            'message' => __('Created user successfully'),
            'user' => HashID::encodeRecord($user),
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
        $userHash = JWTData::fromRequest($request)->get(JWTData::USER_HASH);
        $userId = HashID::decodeSingle($userHash);

        return $this->authorization->hasRole($userId, Role::USERS_CREATE);
    }
}