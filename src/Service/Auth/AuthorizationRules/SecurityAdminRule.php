<?php

namespace App\Service\Auth\AuthorizationRules;

use App\Service\Auth\AuthorizationService;
use App\Service\Auth\JWT\JWTData;
use App\Service\ID\HashID;
use App\Type\Auth\Role;
use Psr\Http\Message\ServerRequestInterface;

class SecurityAdminRule implements AuthorizationRuleInterface
{
    private AuthorizationService $authorization;

    /**
     * Constructor
     *
     * @param AuthorizationService $authorization
     */
    public function __construct(AuthorizationService $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * Allow all security admins everything
     *
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    public function process(ServerRequestInterface $request): bool
    {
        $userHash = JWTData::fromRequest($request)->get(JWTData::USER_HASH);
        $userId = HashID::decodeSingle($userHash);

        return $this->authorization->hasRole($userId, Role::SECURITY_ADMIN);
    }
}