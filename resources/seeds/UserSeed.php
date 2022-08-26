<?php

use App\Type\Auth\Group;
use App\Type\Auth\Role;
use App\Type\Language;
use App\Type\User\RegistrationMethod;
use Phinx\Seed\AbstractSeed;

class UserSeed extends AbstractSeed
{
    public const SECURITY_ADMIN = 'security_admin';
    public const ADMIN = 'admin';
    public const USER = 'user';

    public const USER_ID = [
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
                'id' => self::USER_ID[self::SECURITY_ADMIN],
                'language_id' => LanguageSeed::LANGUAGE_ID[Language::EN_GB],
                'username' => self::SECURITY_ADMIN,
                'email' => 'security_admin@your-domain.com',
                'email_verified' => true,
                'registration_method' => RegistrationMethod::DEFAULT,
                'password' => password_hash(self::SECURITY_ADMIN, PASSWORD_DEFAULT),
                'first_name' => 'Security Admin',
                'last_name' => 'App',
                'last_login_at' => '2022-08-01 00:00:00',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => self::USER_ID['security_admin'],
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => self::USER_ID['security_admin'],
            ],
            [
                'id' => self::USER_ID['admin'],
                'language_id' => LanguageSeed::LANGUAGE_ID[Language::EN_GB],
                'username' => 'admin',
                'email' => 'admin@your-domain.com',
                'email_verified' => true,
                'registration_method' => RegistrationMethod::DEFAULT,
                'password' => password_hash('admin', PASSWORD_DEFAULT),
                'first_name' => 'Administrator',
                'last_name' => 'App',
                'last_login_at' => '2022-08-01 00:00:00',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => self::USER_ID['admin'],
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => self::USER_ID['admin'],
            ],
            [
                'id' => self::USER_ID['user'],
                'language_id' => LanguageSeed::LANGUAGE_ID[Language::EN_GB],
                'username' => 'user',
                'email' => 'user@your-domain.com',
                'email_verified' => true,
                'registration_method' => RegistrationMethod::DEFAULT,
                'password' => password_hash('user', PASSWORD_DEFAULT),
                'first_name' => 'User',
                'last_name' => 'App',
                'last_login_at' => '2022-08-01 00:00:00',
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => self::USER_ID['user'],
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => self::USER_ID['user'],
            ],
        ];

        $this->table('user')->insert($data)->save();

        $userHasGroup = [
            [
                'user_id' => self::USER_ID[self::SECURITY_ADMIN],
                'group_id' => ACLSeed::GROUP_ID[Group::SECURITY_ADMIN],
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'user_id' => self::USER_ID[self::ADMIN],
                'group_id' => ACLSeed::GROUP_ID[Group::ADMIN],
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'user_id' => self::USER_ID[self::USER],
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
                'user_id' => self::USER_ID[self::ADMIN],
                'role_id' => ACLSeed::ROLE_ID[Role::ADMIN],
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'user_id' => self::USER_ID[self::USER],
                'role_id' => ACLSeed::ROLE_ID[Role::USER],
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
        ];

        $this->table('user_has_role')->insert($userHasRole)->save();
    }
}
