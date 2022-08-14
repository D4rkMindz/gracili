<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface;

/**
 * AuthorizationInterface
 * Add this interface to any action that needs to have an authorization.
 * If any logged in user should be able to request the resource, just make the method return true.
 */
interface AuthorizationInterface
{
    /**
     * This method verifies, that a user is actually allowed to request a resource
     *
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    public function authorize(ServerRequestInterface $request): bool;
}