<?php

namespace App\Middleware;

use App\Exception\AuthenticationException;
use App\Exception\JWTExpiredException;
use App\Exception\OAuthException;
use App\Exception\RecordNotFoundException;
use App\Exception\ValidationException;
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
use Slim\Exception\HttpNotFoundException;
use Throwable;

/**
 * Class ExceptionMiddleware.
 */
class ExceptionMiddleware implements MiddlewareInterface
{
    private const JWT_EXPIRED = 'jwt_expired';
    private const NOT_FOUND = 'not_found';
    private const NOT_AUTHORIZED = 'not_authorized';
    private const INVALID_DATA = 'invalid_data';
    private const OAUTH = 'oauth';
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
            'error_type' => 'error',
        ];
        try {
            return $handler->handle($request);
        } catch (ValidationException $validationException) {
            return $this->handleValidationException($validationException);
        } catch (AuthenticationException $authenticationException) {
            return $this->handleAuthenticationException($authenticationException);
        } catch (RecordNotFoundException $recordNotFoundException) {
            return $this->handleRecordNotFoundException($recordNotFoundException, $data);
        } catch (JWTExpiredException $jwtExpiredException) {
            return $this->handleJWTExpiredException($data);
        } catch (OAuthException $oAuthException) {
            return $this->handleOAuthException($oAuthException, $data);
        } catch (HttpNotFoundException $notFoundException) {
            return $this->handleNotFoundException($notFoundException, $data);
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
     * Handle a validation exception
     *
     * @param ValidationException $validationException
     *
     * @return ResponseInterface
     */
    private function handleValidationException(ValidationException $validationException): ResponseInterface
    {
        $responseData = [
            'success' => false,
            'message' => $validationException->getMessage(),
            'error_type' => self::INVALID_DATA,
            'error' => $validationException->getValidationResult()->toArray(),
        ];
        $response = $this->responseFactory->createResponse(HttpCode::UNPROCESSABLE_ENTITY);

        return $this->encoder->encode($response, $responseData);
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
            'message' => $errorMessage,
            'success' => false,
            'error_type' => self::NOT_AUTHORIZED,
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
    private function handleRecordNotFoundException(
        RecordNotFoundException $recordNotFoundException,
        array $data
    ): ResponseInterface {
        $message = $recordNotFoundException->getMessage() . ' (' . $recordNotFoundException->getLocator() . ')';
        $error = $recordNotFoundException;

        $reference = UUID::generate();
        $this->logError($message, $error, $reference);

        $response = $this->responseFactory->createResponse(HttpCode::NOT_FOUND);
        $data['success'] = false;
        $data['message'] = __('Not found');
        $data['error_message'] = $recordNotFoundException->getUserMessage();
        $data['error_type'] = self::NOT_FOUND;

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
     * Hanlde JWT expired
     *
     * @param array $data
     *
     * @return ResponseInterface
     */
    private function handleJWTExpiredException(array $data): ResponseInterface
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
    private function handleOAuthException(OAuthException $oAuthException, array $data): ResponseInterface
    {
        $data['success'] = false;
        $data['message'] = $oAuthException->getMessage();
        $data['jwt_expired'] = true;
        $data['error_type'] = self::OAUTH;
        $response = $this->responseFactory->createResponse(HttpCode::UNAUTHORIZED);

        return $this->encoder->encode($response, $data);
    }

    /**
     * Handle not found
     *
     * @param HttpNotFoundException $notFoundException
     * @param array                 $data
     *
     * @return ResponseInterface
     */
    private function handleNotFoundException(HttpNotFoundException $notFoundException, array $data): ResponseInterface
    {
        $data['success'] = false;
        $data['message'] = __('404 - Not found');
        $data['error_type'] = self::NOT_FOUND;

        $response = $this->responseFactory->createResponse(HttpCode::NOT_FOUND);

        return $this->encoder->encode($response, $data);
    }
}
