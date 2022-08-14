<?php

namespace App\Service\Encoder;

use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Factory\StreamFactory;

/**
 * Class JSONEncoder
 */
class JSONEncoder implements EncoderInterface
{
    private LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Return JSON
     *
     * @param ResponseInterface $response
     * @param array             $data
     *
     * @return ResponseInterface
     */
    public function encode(ResponseInterface $response, array $data): ResponseInterface
    {
        if (!isset($data['message']) || !isset($data['success'])) {
            $this->logger->error('Message or success flag not set', [
                'data' => $data,
            ]);
            $data['message'] = isset($data['message']) ? $data['message'] : __('No message available, please contact the support');
            $data['success'] = false;
        }

        $json = json_encode($data);
        $body = (new StreamFactory())->createStream();
        $body->write($json);

        return $response->withBody($body)->withAddedHeader('Content-Type', 'application/json');
    }
}
