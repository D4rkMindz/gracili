<?php

namespace App\Middleware;

use App\Exception\AuthenticationException;
use App\Exception\RecordNotFoundException;
use App\Service\Encoder\JSONEncoder;
use App\Service\ID\UUID;
use App\Service\SettingsInterface;
use App\Type\HttpCode;
use Exception;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Throwable;

/**
 * Class ExceptionMiddleware.
 */
class ExceptionMiddleware implements MiddlewareInterface
{
    private JSONEncoder $encoder;
    private ResponseFactoryInterface $responseFactory;
    private bool $debug;
    private LoggerInterface $logger;

    /**
     * ExceptionMiddleware constructor.
     *
     * @param JSONEncoder              $encoder
     * @param ResponseFactoryInterface $responseFactory
     * @param SettingsInterface        $settings
     * @param LoggerInterface          $logger
     */
    public function __construct(
        JSONEncoder $encoder,
        ResponseFactoryInterface $responseFactory,
        SettingsInterface $settings,
        LoggerInterface $logger
    ) {
        $this->encoder = $encoder;
        $this->responseFactory = $responseFactory;
        $this->debug = $settings->get('debug');
        $this->logger = $logger;
    }

    /**
     * The called method.
     *
     * This method will be invoked if a middleware is executed
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     * @throws Exception
     * @throws Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = [
            'error' => true,
            'message' => 'error',
        ];
        try {
            return $handler->handle($request);
        } catch (AuthenticationException $authenticationException) {
            return $this->handleAuthenticationException($authenticationException);
        } catch (RecordNotFoundException $recordNotFoundException) {
            return $this->handleRecordNotFoundException($recordNotFoundException, $data);
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            $error = $exception;
        } catch (Throwable $throwable) {
            $message = $throwable->getMessage();
            $error = $throwable;
        }
        //
        $reference = UUID::generate();
        $this->logError($message, $error, $reference);

        if ($this->debug) {
            error_reporting(E_ALL);
            throw $error;
        }

        $response = $this->responseFactory->createResponse(200);
        return $this->encoder->encode($response, $data);
    }

    /**
     * Log the error
     *
     * @param string    $message
     * @param Throwable $throwable
     * @param string    $reference
     */
    private function logError(string $message, Throwable $throwable, string $reference)
    {
        $reflection = new ReflectionClass($throwable);
        $this->logger->error($message . PHP_EOL . 'REF:' . $reference . PHP_EOL . $reflection->getShortName() . ' was catched', [
            'message' => $message,
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'trace' => $throwable->getTraceAsString(),
        ]);
    }

    /**
     * @param Exception|AuthenticationException $authenticationException
     *
     * @return ResponseInterface
     */
    private function handleAuthenticationException(Exception|AuthenticationException $authenticationException): ResponseInterface
    {
        $statusCode = $authenticationException->getStatusCode() ?: HttpCode::UNAUTHORIZED;
        $response = $this->responseFactory->createResponse($statusCode);
        $errorMessage = $authenticationException->getMessage();
        $data = [
            'message' => __('Authentication failed'),
            'success' => false,
            'error' => [
                'message' => $errorMessage,
                'fields' => [
                    [
                        'message' => $errorMessage,
                        'field' => 'username',
                    ],
                ],
            ]
        ];

        return $this->encoder->encode($response, $data);
    }

    /**
     * @param RecordNotFoundException|Exception $recordNotFoundException
     * @param array                             $data
     *
     * @return ResponseInterface
     */
    public function handleRecordNotFoundException(
        RecordNotFoundException|Exception $recordNotFoundException,
        array $data
    ): ResponseInterface {
        $message = $recordNotFoundException->getMessage() . ' (' . $recordNotFoundException->getLocator() . ')';
        $error = $recordNotFoundException;

        $reference = UUID::generate();
        $this->logError($message, $error, $reference);

        $response = $this->responseFactory->createResponse(404);
        $data['success'] = false;
        $data['message'] = __('Not found');

        return $this->encoder->encode($response, $data);
    }
}
