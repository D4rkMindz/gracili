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
use Psr\Log\LoggerInterface;

/**
 * Class GroupArchiveAction
 */
class GroupArchiveAction implements AuthorizationInterface
{
    public const NAME = 'api.v1.group.archive';

    private AuthorizationService $authorization;
    private JSONEncoder $json;
    private LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param GroupService $group
     * @param AuthorizationService $authorization
     * @param JSONEncoder $json
     * @param LoggerInterface $logger
     */
    public function __construct(
        GroupService $group,
        AuthorizationService $authorization,
        JSONEncoder $json,
        LoggerInterface $logger
    ) {
        $this->group = $group;
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

        $groupHash = $args['group_hash'];
        $groupId = HashID::decodeSingle($groupHash);

        // to ensure that you'll receive a not found if already archived
        $this->group->getGroup($groupId);

        $this->group->archive($groupId, $executorId);
        $this->logger->info('User ' . $executorId . ' just archived the group ' . $groupId);

        return $this->json->encode($response, [
            'success' => true,
            'message' => __('Archived group successfully'),
            'group_id' => $groupHash,
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
        $canArchive = $this->authorization->hasRole($groupId, Role::GROUPS_ARCHIVE);

        return $isCreator || $isModifier || $canArchive;
    }
}