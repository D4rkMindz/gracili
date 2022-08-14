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
        Role::GUEST => 10000,
        Role::MONITORING_QUEUE => 9100,
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
