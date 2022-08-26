<?php

namespace App\Controller\User;

use App\Controller\AuthorizationInterface;
use App\Service\Auth\AuthorizationService;
use App\Service\Auth\JWT\JWTData;
use App\Service\Encoder\JSONEncoder;
use App\Service\ID\HashID;
use App\Service\User\UserService;
use App\Type\Auth\Role;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Class UserDeleteAction
 */
class UserDeleteAction implements AuthorizationInterface
{
    public const NAME = 'api.v1.user.delete';
    private UserService $user;
    private AuthorizationService $authorization;
    private JSONEncoder $json;
    private LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param UserService $user
     * @param AuthorizationService $authorization
     * @param JSONEncoder $json
     * @param LoggerInterface $logger
     */
    public function __construct(
        UserService $user,
        AuthorizationService $authorization,
        JSONEncoder $json,
        LoggerInterface $logger
    ) {
        $this->user = $user;
        $this->authorization = $authorization;
        $this->json = $json;
        $this->logger = $logger;
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
        $executorHash = JWTData::fromRequest($request)->get(JWTData::USER_HASH);
        $executorId = HashID::decodeSingle($executorHash);

        $userHash = $args['user_hash'];
        $userId = HashID::decodeSingle($userHash);

        // to ensure that you'll receive a not found if already deleted / not found
        $this->user->getUser($userId);

        $this->user->delete($userId);
        $this->logger->info('User ' . $executorId . ' just deleted the user ' . $userId);

        return $this->json->encode($response, [
            'success' => true,
            'message' => __('Deleted user successfully'),
            'user_id' => $userHash,
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
        // deletion should either be via cronjob or security admin
        // $id = HashID::decodeSingle($args['user_hash']);
        // $isCreator = $this->authorization->isCreator($userId, UserTable::getName(), $id);
        // $isModifier = $this->authorization->isEditor($userId, UserTable::getName(), $id);
        $canDelete = $this->authorization->hasRole($userId, Role::USERS_DELETE);

        return $canDelete;
    }
}