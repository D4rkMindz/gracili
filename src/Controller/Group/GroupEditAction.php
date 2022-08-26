<?php

namespace App\Controller\Group;

use App\Controller\AuthorizationInterface;
use App\Exception\RecordUpdateException;
use App\Service\Auth\AuthorizationService;
use App\Service\Auth\GroupService;
use App\Service\Auth\JWT\JWTData;
use App\Service\Encoder\JSONEncoder;
use App\Service\ID\HashID;
use App\Table\GroupTable;
use App\Type\Auth\Role;
use App\Util\ArrayReader;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class GroupEditAction
 */
class GroupEditAction implements AuthorizationInterface
{
    public const NAME = 'api.v1.group.edit';

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
        $groupId = HashID::decodeSingle($args['group_hash']);
        $data = new ArrayReader($request->getParsedBody());

        $updated = $this->group->modifyGroup(
            $groupId,
            $executorId,
            $data->findString('name'),
            $data->findString('description'),
        );

        if (!$updated) {
            throw new RecordUpdateException(__('Updating group failed'), 'id = ' . $groupId);
        }
        $group = $this->group->getGroup($groupId);

        return $this->json->encode($response, [
            'message' => __('Updated group successfully'),
            'success' => true,
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
        $groupHash = JWTData::fromRequest($request)->get(JWTData::USER_HASH);
        $groupId = HashID::decodeSingle($groupHash);
        $id = HashID::decodeSingle($args['group_hash']);
        $isCreator = $this->authorization->isCreator($groupId, GroupTable::getName(), $id);
        $isModifier = $this->authorization->isEditor($groupId, GroupTable::getName(), $id);
        $canWrite = $this->authorization->hasRole($groupId, Role::GROUPS_WRITE);

        return $isCreator || $isModifier || $canWrite;
    }
}