<?php

namespace App\Type\Auth;

class Role
{
    public const SECURITY_ADMIN = 'role.admin.security';
    public const ADMIN = 'role.admin';
    public const USER = 'role.user';
    public const GUEST = 'role.guest';

    public const MONITORING_QUEUE = 'role.monitoring.queue.view';
}