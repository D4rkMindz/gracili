<?php

namespace App\Service\Encoder;

use Psr\Http\Message\ResponseInterface;

/**
 * Class EncoderInterface
 */
interface EncoderInterface
{
    /**
     * Encode a response.
     *
     * @param ResponseInterface $response
     * @param array             $data
     *
     * @return ResponseInterface
     */
    public function encode(ResponseInterface $response, array $data): ResponseInterface;
}
