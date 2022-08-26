<?php


use App\Type\Auth\Group;
use App\Type\Auth\Role;
use Phinx\Seed\AbstractSeed;

class ACLSeed extends AbstractSeed
{
    public const ROLE_ID = [
        Role::SECURITY_ADMIN => 100,
        Role::ADMIN => 200,
        Role::USER => 1000,
        Role::USERS_READ => 2000,
        Role::USERS_WRITE => 2100,
        Role::USERS_CREATE => 2200,
        Role::USERS_ARCHIVE => 2300,
        Role::USERS_DELETE => 2999,
        Role::GROUPS_READ => 3000,
        Role::GROUPS_WRITE => 3100,
        Role::GROUPS_CREATE => 3200,
        Role::GROUPS_ARCHIVE => 3300,
        Role::GROUPS_DELETE => 3999,
        Role::ROLES_READ => 4000,
        Role::ROLES_WRITE => 4100,
        Role::ROLES_CREATE => 4200,
        Role::ROLES_ARCHIVE => 4300,
        Role::ROLES_DELETE => 4999,
        Role::MONITORING_QUEUE => 9100,
        Role::GUEST => 10000,
    ];

    public const GROUP_ID = [
        Group::SECURITY_ADMIN => 100,
        Group::ADMIN => 200,
        Group::USER => 1000,
        Group::MONITORING_VIEWER => 9000,
    ];

    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run()
    {
        $this->insertRoles();

        $this->insertGroups();

        $this->addRolesToGroups();
    }

    /**
     * Insert all roles
     *
     * @return void
     */
    public function insertRoles(): void
    {
        $roles = [
            [
                'id' => self::ROLE_ID[Role::SECURITY_ADMIN],
                'name' => Role::SECURITY_ADMIN,
                'description' => 'The absolute administrator role. Can do everything. Only assign people you trust',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'id' => self::ROLE_ID[Role::ADMIN],
                'name' => Role::ADMIN,
                'description' => 'The administrator role. Can do everything that is NOT security related. Only assign people you trust',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'id' => self::ROLE_ID[Role::USER],
                'name' => Role::USER,
                'description' => 'The user role. This role is the minimum role a user must have to be able to login.',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'id' => self::ROLE_ID[Role::GUEST],
                'name' => Role::GUEST,
                'description' => 'The guest role. This role is for users that are not identified (yet).',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'id' => self::ROLE_ID[Role::USERS_READ],
                'name' => Role::USERS_READ,
                'description' => 'The user reading role. This role is to read ALL user data.',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'id' => self::ROLE_ID[Role::USERS_WRITE],
                'name' => Role::USERS_WRITE,
                'description' => 'The user editing role. This role is to edit ALL user data.',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'id' => self::ROLE_ID[Role::USERS_CREATE],
                'name' => Role::USERS_CREATE,
                'description' => 'The user creator role. This role is to create users.',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'id' => self::ROLE_ID[Role::USERS_ARCHIVE],
                'name' => Role::USERS_ARCHIVE,
                'description' => 'The user archiver. This role is to archive users.',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'id' => self::ROLE_ID[Role::USERS_DELETE],
                'name' => Role::USERS_DELETE,
                'description' => 'The user creator role. This role is to delete users. HANLDE WITH CARE',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'id' => self::ROLE_ID[Role::GROUPS_READ],
                'name' => Role::GROUPS_READ,
                'description' => 'The group reading role. This role is to read ALL group data.',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'id' => self::ROLE_ID[Role::GROUPS_WRITE],
                'name' => Role::GROUPS_WRITE,
                'description' => 'The group editing role. This role is to edit ALL group data.',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'id' => self::ROLE_ID[Role::GROUPS_CREATE],
                'name' => Role::GROUPS_CREATE,
                'description' => 'The group creator role. This role is to create groups.',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'id' => self::ROLE_ID[Role::GROUPS_ARCHIVE],
                'name' => Role::GROUPS_ARCHIVE,
                'description' => 'The group archiver. This role is to archive groups.',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'id' => self::ROLE_ID[Role::GROUPS_DELETE],
                'name' => Role::GROUPS_DELETE,
                'description' => 'The group creator role. This role is to delete groups. HANLDE WITH CARE',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'id' => self::ROLE_ID[Role::ROLES_READ],
                'name' => Role::ROLES_READ,
                'description' => 'The role reading role. This role is to read ALL role data.',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'id' => self::ROLE_ID[Role::ROLES_WRITE],
                'name' => Role::ROLES_WRITE,
                'description' => 'The role editing role. This role is to edit ALL role data.',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'id' => self::ROLE_ID[Role::ROLES_CREATE],
                'name' => Role::ROLES_CREATE,
                'description' => 'The role creator role. This role is to create roles.',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'id' => self::ROLE_ID[Role::ROLES_ARCHIVE],
                'name' => Role::ROLES_ARCHIVE,
                'description' => 'The role archiver. This role is to archive roles.',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'id' => self::ROLE_ID[Role::ROLES_DELETE],
                'name' => Role::ROLES_DELETE,
                'description' => 'The role creator role. This role is to delete roles. HANLDE WITH CARE',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'id' => self::ROLE_ID[Role::MONITORING_QUEUE],
                'name' => Role::MONITORING_QUEUE,
                'description' => 'The monitoring role. This role is for users that are allowed to view the monitoring of the queue workers.',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
        ];

        $this->table('role')->insert($roles)->save();
    }

    /**
     * Insert all groups
     *
     * @return void
     */
    public function insertGroups(): void
    {
        $groups = [
            [
                'id' => self::GROUP_ID[Group::SECURITY_ADMIN],
                'name' => Group::SECURITY_ADMIN,
                'description' => 'The absolute administrator group. Contains all roles. Only assign people you trust',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'id' => self::GROUP_ID[Group::ADMIN],
                'name' => Group::ADMIN,
                'description' => 'The administrator group. Contains all roles except the security admin role. Only assign people you trust',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'id' => self::GROUP_ID[Group::USER],
                'name' => Group::USER,
                'description' => 'The regular user group. Contains the roles required to use the basic functionality of the app. This is the default group',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'id' => self::GROUP_ID[Group::MONITORING_VIEWER],
                'name' => Group::MONITORING_VIEWER,
                'description' => 'The monitoring user group. Contains the roles required to use the monitoring apis of the app.',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
        ];

        $this->table('group')->insert($groups)->save();
    }

    /**
     * Add the roles to the corresponding groups
     *
     * @return void
     */
    public function addRolesToGroups(): void
    {
        $groupHasRoles = [];

        $allRoles = self::ROLE_ID;

        foreach ($allRoles as $roleId) {
            $groupHasRoles[] = [
                'group_id' => self::GROUP_ID[Group::SECURITY_ADMIN],
                'role_id' => $roleId,
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ];
        }
        $groupHasRoles[] = [
            'group_id' => self::GROUP_ID[Group::ADMIN],
            'role_id' => self::ROLE_ID[Role::ADMIN],
            'created_at' => '2022-08-01 00:00:00',
            'created_by' => 0,
            'modified_at' => '2022-08-01 00:00:00',
            'modified_by' => 0,
        ];
        $groupHasRoles[] = [
            'group_id' => self::GROUP_ID[Group::ADMIN],
            'role_id' => self::ROLE_ID[Role::USERS_READ],
            'created_at' => '2022-08-01 00:00:00',
            'created_by' => 0,
            'modified_at' => '2022-08-01 00:00:00',
            'modified_by' => 0,
        ];
        $groupHasRoles[] = [
            'group_id' => self::GROUP_ID[Group::ADMIN],
            'role_id' => self::ROLE_ID[Role::USERS_WRITE],
            'created_at' => '2022-08-01 00:00:00',
            'created_by' => 0,
            'modified_at' => '2022-08-01 00:00:00',
            'modified_by' => 0,
        ];
        $groupHasRoles[] = [
            'group_id' => self::GROUP_ID[Group::ADMIN],
            'role_id' => self::ROLE_ID[Role::USERS_CREATE],
            'created_at' => '2022-08-01 00:00:00',
            'created_by' => 0,
            'modified_at' => '2022-08-01 00:00:00',
            'modified_by' => 0,
        ];
        $groupHasRoles[] = [
            'group_id' => self::GROUP_ID[Group::ADMIN],
            'role_id' => self::ROLE_ID[Role::USERS_ARCHIVE],
            'created_at' => '2022-08-01 00:00:00',
            'created_by' => 0,
            'modified_at' => '2022-08-01 00:00:00',
            'modified_by' => 0,
        ];

        $groupHasRoles[] = [
            'group_id' => self::GROUP_ID[Group::USER],
            'role_id' => self::ROLE_ID[Role::USER],
            'created_at' => '2022-08-01 00:00:00',
            'created_by' => 0,
            'modified_at' => '2022-08-01 00:00:00',
            'modified_by' => 0,
        ];

        $this->table('group_has_role')->insert($groupHasRoles)->save();
    }
}
