<?php

namespace App\Util;

use Psr\Http\Message\ServerRequestInterface;
use PSR7Sessions\Storageless\Http\SessionMiddleware;
use PSR7Sessions\Storageless\Session\SessionInterface;

/**
 * Class SessionHelper
 */
class SessionHelper
{
    /**
     * Get a key from the session
     *
     * @param string                 $key
     * @param ServerRequestInterface $request
     *
     * @return string|array|bool|int|float|null
     */
    public static function get(string $key, ServerRequestInterface $request)
    {
        return self::extract($request)->get($key);
    }

    /**
     * Extract the session
     *
     * @param ServerRequestInterface $request
     *
     * @return SessionInterface
     */
    public static function extract(ServerRequestInterface $request): SessionInterface
    {
        return $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
    }
}