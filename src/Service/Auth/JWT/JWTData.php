<?php

namespace App\Service\Auth\JWT;

use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class JWTData
 */
class JWTData
{
    public const USER_HASH = 'id';
    public const LAST_LOGIN = 'last_login';
    public const ROLES = 'roles';
    public const GROUPS = 'groups';
    public const LOCALE = 'locale';

    public const REQUEST_ATTRIBUTE = 'jwt_token';

    private array $data;

    /**
     * JWTData constructor.
     *
     * @param stdClass|null $data
     */
    public function __construct(?stdClass $data)
    {
        $this->data = json_decode(json_encode($data), true);
    }

    /**
     * parse out of request
     *
     * @param ServerRequestInterface $request
     *
     * @return static
     */
    public static function fromRequest(ServerRequestInterface $request): self
    {
        $jwt = $request->getAttribute(JWTData::REQUEST_ATTRIBUTE);

        if (isset($jwt['data'])) {
            return $jwt['data'];
        }

        return new JWTData(new stdClass());
    }

    /**
     * Get a value from the jwt data
     *
     * @param string|null $path
     * @param null        $default
     *
     * @return array|mixed|null
     */
    public function get(?string $path, $default = null): mixed
    {
        if (is_null($path)) {
            return $this->data;
        }

        $items = $this->data;
        foreach (explode('.', $path) as $segment) {
            if (!is_array($items) || !array_key_exists($segment, $items)) {
                return $default;
            }

            $items = &$items[$segment];
        }

        return $items;
    }
}
