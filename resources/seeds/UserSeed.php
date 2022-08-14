<?php

use App\Type\Auth\Group;
use App\Type\Auth\Role;
use App\Type\Language;
use Phinx\Seed\AbstractSeed;

class UserSeed extends AbstractSeed
{
    public const USER = [
        'security_admin' => 1,
        'admin' => 2,
        'user' => 3,
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
        $data = [
            [
                'id' =>  self::USER['security_admin'],
                'language_id' => LanguageSeed::LANGUAGE_ID[Language::EN_GB],
                'username' => 'security_admin',
                'email' => 'security_admin@your-domain.com',
                'password' => password_hash('security_admin', PASSWORD_DEFAULT),
                'first_name' => 'Security äAdmin',
                'last_name' => 'App',
                'last_login_at' => '2022-08-01 00:00:00',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'id' =>  self::USER['admin'],
                'language_id' => LanguageSeed::LANGUAGE_ID[Language::EN_GB],
                'username' => 'admin',
                'email' => 'admin@your-domain.com',
                'password' => password_hash('admin', PASSWORD_DEFAULT),
                'first_name' => 'Administrator',
                'last_name' => 'App',
                'last_login_at' => '2022-08-01 00:00:00',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'id' => self::USER['user'],
                'language_id' => LanguageSeed::LANGUAGE_ID[Language::EN_GB],
                'username' => 'user',
                'email' => 'user@your-domain.com',
                'password' => password_hash('user', PASSWORD_DEFAULT),
                'first_name' => 'User',
                'last_name' => 'App',
                'last_login_at' => '2022-08-01 00:00:00',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
        ];

        $this->table('user')->insert($data)->save();

        $userHasGroup = [
            [
                'user_id' =>  self::USER['security_admin'],
                'group_id' => ACLSeed::GROUP_ID[Group::SECURITY_ADMIN],
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'user_id' =>  self::USER['admin'],
                'group_id' => ACLSeed::GROUP_ID[Group::ADMIN],
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'user_id' =>  self::USER['user'],
                'group_id' => ACLSeed::GROUP_ID[Group::USER],
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
        ];

        $this->table('user_has_group')->insert($userHasGroup)->save();

        $userHasRole = [
            [
                'user_id' =>  self::USER['admin'],
                'role_id' => ACLSeed::ROLE_ID[Role::ADMIN],
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
        ];

        $this->table('user_has_role')->insert($userHasRole)->save();
    }
}
