<?php

namespace App\Controller;

use App\Service\Encoder\JSONEncoder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Class IndexAction
 */
class IndexAction
{
    public const ROUTE = 'root';

    private JSONEncoder $json;
    private LoggerInterface $logger;

    /**
     * IndexController constructor.
     *
     * @param JSONEncoder     $json
     * @param LoggerInterface $logger
     */
    public function __construct(
        JSONEncoder $json,
        LoggerInterface $logger
    ) {
        $this->json = $json;
        $this->logger = $logger;
    }

    /**
     * View index page
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = [
            'success' => true,
            'message' => __('Welcome to Your-App API'),
            'documentation_url' => 'https://your-domain.com/docs',
        ];

        return $this->json->encode($response, $data);
    }
}