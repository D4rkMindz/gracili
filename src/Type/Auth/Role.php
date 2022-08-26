<?php

namespace App\Type\Auth;

class Role
{
    public const SECURITY_ADMIN = 'role.admin.security';
    public const ADMIN = 'role.admin';
    public const USER = 'role.user';
    public const GUEST = 'role.guest';

    public const MONITORING_QUEUE = 'role.monitoring.queue.view';
    public const USERS_READ = 'role.users.read';
    public const USERS_CREATE = 'role.users.create';
    public const USERS_WRITE = 'role.users.write';
    public const USERS_ARCHIVE = 'role.users.archive';
    public const USERS_DELETE = 'role.users.delete';

    public const GROUPS_READ = 'role.groups.read';
    public const GROUPS_CREATE = 'role.groups.create';
    public const GROUPS_WRITE = 'role.groups.write';
    public const GROUPS_ARCHIVE = 'role.groups.archive';
    public const GROUPS_DELETE = 'role.groups.delete';

    public const ROLES_READ = 'role.roles.read';
    public const ROLES_CREATE = 'role.roles.create';
    public const ROLES_WRITE = 'role.roles.write';
    public const ROLES_ARCHIVE = 'role.roles.archive';
    public const ROLES_DELETE = 'role.roles.delete';
}