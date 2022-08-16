<?php

namespace App\Middleware;

use App\Exception\AuthenticationException;
use App\Exception\JWTExpiredException;
use App\Exception\OAuthException;
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

    private const JWT_EXPIRED = 'jwt_expired';
    private const NOT_FOUND = 'not_found';
    private const AUTHENTICATION_FAILED = 'authentication_failed';
    private const OAUTH = 'oauth';

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
            'error_type' => 'error',
        ];
        try {
            return $handler->handle($request);
        } catch (AuthenticationException $authenticationException) {
            return $this->handleAuthenticationException($authenticationException);
        } catch (RecordNotFoundException $recordNotFoundException) {
            return $this->handleRecordNotFoundException($recordNotFoundException, $data);
        } catch (JWTExpiredException $jwtExpiredException) {
            return $this->handleJWTExpiredException($data);
        } catch (OAuthException $oAuthException) {
            return $this->handleOAuthException($oAuthException, $data);
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            $error = $exception;
        } catch (Throwable $throwable) {
            $message = $throwable->getMessage();
            $error = $throwable;
        }

        $reference = UUID::generate();
        $this->logError($message, $error, $reference);

        if ($this->debug) {
            error_reporting(E_ALL);
            throw $error;
        }

        $response = $this->responseFactory->createResponse(HttpCode::INTERNAL_SERVER_ERROR);

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
        $this->logger->error($message . PHP_EOL . 'REF:' . $reference . PHP_EOL . $reflection->getShortName() . ' was catched',
            [
                'message' => $message,
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'trace' => $throwable->getTraceAsString(),
            ]);
    }

    /**
     * @param AuthenticationException $authenticationException
     *
     * @return ResponseInterface
     */
    private function handleAuthenticationException(
        AuthenticationException $authenticationException
    ): ResponseInterface {
        $statusCode = $authenticationException->getStatusCode() ?: HttpCode::UNAUTHORIZED;
        $response = $this->responseFactory->createResponse($statusCode);
        $errorMessage = $authenticationException->getMessage();
        $data = [
            'message' => __('Authentication failed'),
            'success' => false,
            'error_type' => self::AUTHENTICATION_FAILED,
            'error' => [
                'message' => $errorMessage,
                'fields' => [
                    [
                        'message' => $errorMessage,
                        'field' => 'username',
                    ],
                ],
            ],
        ];

        return $this->encoder->encode($response, $data);
    }

    /**
     * Handle record not found
     *
     * @param RecordNotFoundException $recordNotFoundException
     * @param array                   $data
     *
     * @return ResponseInterface
     */
    public function handleRecordNotFoundException(
        RecordNotFoundException $recordNotFoundException,
        array $data
    ): ResponseInterface {
        $message = $recordNotFoundException->getMessage() . ' (' . $recordNotFoundException->getLocator() . ')';
        $error = $recordNotFoundException;

        $reference = UUID::generate();
        $this->logError($message, $error, $reference);

        $response = $this->responseFactory->createResponse(404);
        $data['success'] = false;
        $data['message'] = __('Not found');
        $data['error_type'] = self::NOT_FOUND;

        return $this->encoder->encode($response, $data);
    }

    /**
     * Hanlde JWT expired
     *
     * @param array $data
     *
     * @return ResponseInterface
     */
    public function handleJWTExpiredException(array $data): ResponseInterface
    {
        $data['success'] = false;
        $data['message'] = __('Authentication expired');
        $data['jwt_expired'] = true;
        $data['error_type'] = self::JWT_EXPIRED;
        $response = $this->responseFactory->createResponse(HttpCode::UNAUTHORIZED);

        return $this->encoder->encode($response, $data);
    }

    /**
     * Handle OAuth2.0 exception
     *
     * @param OAuthException $oAuthException
     * @param array          $data
     *
     * @return ResponseInterface
     */
    public function handleOAuthException(OAuthException $oAuthException, array $data): ResponseInterface
    {
        $data['success'] = false;
        $data['message'] = $oAuthException->getMessage();
        $data['jwt_expired'] = true;
        $data['error_type'] = self::OAUTH;
        $response = $this->responseFactory->createResponse(HttpCode::UNAUTHORIZED);

        return $this->encoder->encode($response, $data);
    }
}
