<?php

namespace App\Controller\User;

use App\Controller\AuthorizationInterface;
use App\Exception\RecordUpdateException;
use App\Service\Auth\AuthorizationService;
use App\Service\Auth\JWT\JWTData;
use App\Service\Encoder\JSONEncoder;
use App\Service\ID\HashID;
use App\Service\User\UserService;
use App\Table\UserTable;
use App\Type\Auth\Role;
use App\Util\ArrayReader;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class UserEditAction
 */
class UserEditAction implements AuthorizationInterface
{
    public const NAME = 'api.v1.user.edit';

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
     *
     * @throws RecordUpdateException
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $jwtData = JWTData::fromRequest($request);
        $executorId = HashID::decodeSingle($jwtData->get(JWTData::USER_HASH));
        $userId = HashID::decodeSingle($args['user_hash']);
        $data = new ArrayReader($request->getParsedBody());

        $updated = $this->user->modifyUser(
            $userId,
            $executorId,
            $data->findString('language'),
            $data->findString('username'),
            $data->findString('email'),
            null,
            $data->findString('first_name'),
            $data->findString('last_name'),
        );

        if (!$updated) {
            throw new RecordUpdateException(__('Updating user failed'), 'id = ' . $userId);
        }
        $user = $this->user->getUser($userId);

        return $this->json->encode($response, [
            'message' => __('Updated user successfully'),
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
        $canWrite = $this->authorization->hasRole($userId, Role::USERS_WRITE);

        return $isCreator || $isModifier || $canWrite;
    }
}