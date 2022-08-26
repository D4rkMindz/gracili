<?php

namespace App\Controller\Monitor;

use App\Controller\AuthorizationInterface;
use App\Service\Auth\AuthorizationService;
use App\Service\Auth\JWT\JWTData;
use App\Service\Encoder\JSONEncoder;
use App\Service\ID\HashID;
use App\Type\Auth\Group;
use App\Type\Auth\Role;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class MonitorQueueAction
 */
class MonitorQueueAction implements AuthorizationInterface
{
    public const NAME = 'api.v1.monitoring.queue.get';

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
     * Call the action
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $count = exec('sh ' . __DIR__ . '/../../../bin/enqueue/count.sh');

        return $this->json->encode($response, [
            'count' => $count,
            'message' => __('Found {count} running queue workers', ['count' => $count]),
            'success' => true,
        ]);
    }

    /**
     * This method verifies, that a user is actually allowed to request this resource
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

        // check if the user can only see the queue count
        $isMonitoringViewerForQueue = $this->authorization->hasRole($userId, Role::MONITORING_QUEUE);
        if ($isMonitoringViewerForQueue) {
            return true;
        }

        // check if the user can see monitorings in general
        return $this->authorization->hasGroup($userId, Group::MONITORING_VIEWER);
    }
}