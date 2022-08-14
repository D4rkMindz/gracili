<?php

namespace App\Service\Encoder;

use Intervention\Image\Image;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ImageEncoder
 */
class ImageEncoder
{
    /**
     * Return an image
     *
     * @param ResponseInterface $response
     * @param Image             $image
     *
     * @return ResponseInterface
     */
    public function encode(ResponseInterface $response, Image $image): ResponseInterface
    {
        return $response->withBody($image->stream())
            ->withHeader('Content-Type', $image->mime());
    }
}