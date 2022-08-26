<?php

namespace App\Controller\Auth;

use App\Queue\Email\User\SendWelcomeEmailProcessor;
use App\Service\Encoder\JSONEncoder;
use App\Service\ID\HashID;
use App\Service\User\UserService;
use App\Util\ArrayReader;
use Enqueue\SimpleClient\SimpleClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RegisterAction
 */
class RegisterAction
{
    public const NAME = 'api.v1.auth.register';

    private UserService $user;
    private JSONEncoder $json;
    private SimpleClient $client;

    /**
     * Construct
     *
     * @param UserService  $user
     * @param SimpleClient $client
     * @param JSONEncoder  $json
     */
    public function __construct(UserService $user, SimpleClient $client, JSONEncoder $json)
    {
        $this->user = $user;
        $this->client = $client;
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
        $data = new ArrayReader($request->getParsedBody());
        $userId = $this->user->createUser(
            $data->findString('username', ''),
            $data->findString('email', ''),
            $data->findString('password', ''),
            $data->findString('first_name', ''),
            $data->findString('last_name'),
            $data->findString('language'),
        );

        $this->client->sendCommand(SendWelcomeEmailProcessor::class, [
            SendWelcomeEmailProcessor::KEY_EMAIL => $data->findString('email'),
            SendWelcomeEmailProcessor::KEY_FIRST_NAME => $data->findString('first_name'),
            SendWelcomeEmailProcessor::KEY_USERNAME => $data->findString('username'),
        ]);

        $user = $this->user->getUser($userId);

        return $this->json->encode($response, [
            'success' => true,
            'message' => __('Signed up successfully'),
            'user' => HashID::encodeRecord($user),
        ]);
    }
}