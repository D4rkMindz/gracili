<?php

namespace App\Service\Auth\AuthorizationRules;

use Psr\Http\Message\ServerRequestInterface;

interface AuthorizationRuleInterface
{
    /**
     * Add a specific rule that runs on every request
     *
     * E.g. admin permissions can be handled like this
     *
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    public function process(ServerRequestInterface $request): bool;
}