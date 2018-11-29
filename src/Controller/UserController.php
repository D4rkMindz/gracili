<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\Mailer\MailerInterface;
use App\Service\Validation\UserValidation;
use Interop\Container\Exception\ContainerException;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;

/**
 * Class UserController
 */
class UserController extends AppController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserValidation
     */
    private $userValidation;

    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * UserController constructor.
     *
     * @param Container $container
     * @throws ContainerException
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->userRepository = $container->get(UserRepository::class);
        $this->userValidation = $container->get(UserValidation::class);
        $this->mailer = $container->get(MailerInterface::class);
    }

    /**
     * Get all users.
     *
     * @param Request $request
     * @param Response $response
     * @return ResponseInterface
     */
    public function getAllUsersAction(Request $request, Response $response): ResponseInterface
    {
        $users = $this->userRepository->getAllUsers();
        return $this->json($response, ['users' => $users]);
    }

    /**
     * Get single user.
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     */
    public function getUserAction(Request $request, Response $response, array $args): ResponseInterface
    {
        $userId = array_value('user_id', $args);
        $users = $this->userRepository->getUser($userId);
        return $this->json($response, ['users' => $users]);
    }

    /**
     * Create a user.
     *
     * @param Request $request
     * @param Response $response
     * @return ResponseInterface
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    public function createUserAction(Request $request, Response $response): ResponseInterface
    {
        $data = $this->getParsedBody($request);
        $username = array_value('username', $data);
        $email = array_value('email', $data);
        $password = array_value('password', $data);

        $validationResult = $this->userValidation->validateCreate($username, $email, $password);
        if ($validationResult->fails()) {
            return $this->validationError($response, $validationResult);
        }

        $userId = $this->userRepository->createUser($username, $email, $password);
        if (!empty($userId)) {
            $this->logger->info("Created a new user:\nUsername: {$username}\nEmail: {$email}");

            $viewData = [
                'link' => 'https://github.com/d4rkmindz/gracili',
                'contact_mail' => 'contact@gracili.com',
            ];
            $html = $this->twig->render('Email/welcome.twig', $viewData);
            $this->mailer->sendHtml(
                $email,
                __('Thanks for Your Registration at Gracili'),
                $html
            );

            return $this->json($response, ['user_id' => $userId, 'message' => __('User created succesfully')]);
        }

        $this->logger->error("Creating a user failed:\nUsername: {$username}\nEmail: {$email}");

        return $this->error($response, ['message' => __('Something went wrong')]);
    }

    /**
     * Update a user.
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     */
    public function updateUserAction(Request $request, Response $response, array $args): ResponseInterface
    {
        $data = $this->getParsedBody($request);
        $username = array_value('username', $data);
        $email = array_value('email', $data);
        $password = array_value('password', $data);
        $userId = array_value('user_id', $args);
        $executorId = $this->getCurrentUserId();

        $validationResult = $this->userValidation->validateUpdate($username, $email, $password);
        if ($validationResult->fails()) {
            return $this->validationError($response, $validationResult);
        }

        $updated = $this->userRepository->updateUser($userId, $executorId, $username, $email, $password);
        if ($updated) {
            $this->logger->info("Updated a user:\nUsername: {$username}\nEmail: {$email}\nExecutor: {$executorId}");

            return $this->json($response, ['message' => __('User updated successfully')]);
        }

        $this->logger->error("Updating a user failed:\nUsername: {$username}\nEmail: {$email}\nExecutor: {$executorId}");

        return $this->error($response, ['message' => __('Something went wrong')]);
    }

    /**
     * Archive a user.
     *
     * @param Request $request
     * param Response $response
     * @param array $args
     * @return ResponseInterface
     */
    public function archiveUserAction(Request $request, Response $response, array $args): ResponseInterface
    {
        $userId = array_value('user_id', $args);
        $executorId = $this->getCurrentUserId();
        $archived = $this->userRepository->archiveUser($userId, $executorId);
        if ($archived) {
            $this->logger->info("Removed a user:\nID: {$userId}\nExecutor: {$executorId}");

            return $this->json($response, ['message' => __('User deleted successfully')]);
        }
        $this->logger->error("Removing a user failed:\nID: {$userId}\nExecutor: {$executorId}");

        return $this->error($response, ['message' => __('Something went wrong')]);
    }
}
