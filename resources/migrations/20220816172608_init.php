<?php

use Phinx\Db\Adapter\MysqlAdapter;

class Init extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER DATABASE CHARACTER SET 'utf8mb4';");
        $this->execute("ALTER DATABASE COLLATE='utf8mb4_unicode_ci';");
        $this->table('group', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('name', 'string', [
                'null' => false,
                'limit' => 80,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'The name of the group',
                'after' => 'id',
            ])
            ->addColumn('description', 'text', [
                'null' => false,
                'limit' => MysqlAdapter::TEXT_LONG,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'The description of usage for the group',
                'after' => 'name',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'The record creation date time',
                'after' => 'description',
            ])
            ->addColumn('created_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record creator user id',
                'after' => 'created_at',
            ])
            ->addColumn('modified_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'The record last modification date time',
                'after' => 'created_by',
            ])
            ->addColumn('modified_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record last modifier user id',
                'after' => 'modified_at',
            ])
            ->addColumn('archived_at', 'datetime', [
                'null' => true,
                'comment' => 'The record archive date time',
                'after' => 'modified_by',
            ])
            ->addColumn('archived_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record archiver user id',
                'after' => 'archived_at',
            ])
            ->create();
        $this->table('group_has_role', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('group_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
            ->addColumn('role_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'group_id',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'The record creation date time',
                'after' => 'role_id',
            ])
            ->addColumn('created_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record creator user id',
                'after' => 'created_at',
            ])
            ->addColumn('modified_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'The record last modification date time',
                'after' => 'created_by',
            ])
            ->addColumn('modified_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record last modifier user id',
                'after' => 'modified_at',
            ])
            ->addColumn('archived_at', 'datetime', [
                'null' => true,
                'comment' => 'The record archive date time',
                'after' => 'modified_by',
            ])
            ->addColumn('archived_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record archiver user id',
                'after' => 'archived_at',
            ])
            ->addIndex(['group_id'], [
                'name' => 'fk_group_has_role_group1_idx',
                'unique' => false,
            ])
            ->addIndex(['role_id'], [
                'name' => 'fk_group_has_role_role1_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('jwt', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
            ->addColumn('jwt', 'text', [
                'null' => false,
                'limit' => MysqlAdapter::TEXT_LONG,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'The JWT sent to the user',
                'after' => 'user_id',
            ])
            ->addColumn('refresh_token', 'string', [
                'null' => false,
                'limit' => 80,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'The refresh token for the JWT to collect a new token',
                'after' => 'jwt',
            ])
            ->addColumn('issued_at', 'datetime', [
                'null' => false,
                'comment' => 'When the refresh token was issued',
                'after' => 'refresh_token',
            ])
            ->addColumn('expires_at', 'datetime', [
                'null' => false,
                'comment' => 'The expiration date time of the refresh token',
                'after' => 'issued_at',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'The record creation date time',
                'after' => 'expires_at',
            ])
            ->addColumn('created_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record creator user id',
                'after' => 'created_at',
            ])
            ->addColumn('modified_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'The record last modification date time',
                'after' => 'created_by',
            ])
            ->addColumn('modified_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record last modifier user id',
                'after' => 'modified_at',
            ])
            ->addColumn('archived_at', 'datetime', [
                'null' => true,
                'comment' => 'The record archive date time',
                'after' => 'modified_by',
            ])
            ->addColumn('archived_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record archiver user id',
                'after' => 'archived_at',
            ])
            ->addIndex(['user_id'], [
                'name' => 'fk_jwt_token_user1_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('language', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('name', 'string', [
                'null' => false,
                'limit' => 80,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'The name of the language in the foreign language (e.g. „Deutsch“)',
                'after' => 'id',
            ])
            ->addColumn('english_name', 'string', [
                'null' => false,
                'limit' => 80,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'The name of the language in English (e.g. „German“)',
                'after' => 'name',
            ])
            ->addColumn('tag', 'string', [
                'null' => false,
                'limit' => 80,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'The RFC 5646 language tag (e.g. de-CH)',
                'after' => 'english_name',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'The record creation date time',
                'after' => 'tag',
            ])
            ->addColumn('created_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record creator user id',
                'after' => 'created_at',
            ])
            ->addColumn('modified_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'The record last modification date time',
                'after' => 'created_by',
            ])
            ->addColumn('modified_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record last modifier user id',
                'after' => 'modified_at',
            ])
            ->addColumn('archived_at', 'datetime', [
                'null' => true,
                'comment' => 'The record archive date time',
                'after' => 'modified_by',
            ])
            ->addColumn('archived_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record archiver user id',
                'after' => 'archived_at',
            ])
            ->create();
        $this->table('oauth_token', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
            ->addColumn('token', 'text', [
                'null' => false,
                'limit' => MysqlAdapter::TEXT_LONG,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'user_id',
            ])
            ->addColumn('refresh_token', 'string', [
                'null' => false,
                'limit' => 500,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'token',
            ])
            ->addColumn('expires_at', 'datetime', [
                'null' => false,
                'after' => 'refresh_token',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'The record creation date time',
                'after' => 'expires_at',
            ])
            ->addColumn('created_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record creator user id',
                'after' => 'created_at',
            ])
            ->addColumn('modified_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'The record last modification date time',
                'after' => 'created_by',
            ])
            ->addColumn('modified_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record last modifier user id',
                'after' => 'modified_at',
            ])
            ->addColumn('archived_at', 'datetime', [
                'null' => true,
                'comment' => 'The record archive date time',
                'after' => 'modified_by',
            ])
            ->addColumn('archived_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record archiver user id',
                'after' => 'archived_at',
            ])
            ->addIndex(['user_id'], [
                'name' => 'fk_oauth_token_user1_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('password_reset_request', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
            ->addColumn('token', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'A unique token to identify a password reset request',
                'after' => 'user_id',
            ])
            ->addColumn('expires_at', 'datetime', [
                'null' => false,
                'comment' => 'The token expiration date',
                'after' => 'token',
            ])
            ->addColumn('email_sent_at', 'datetime', [
                'null' => true,
                'comment' => 'The date time when the email was sent',
                'after' => 'expires_at',
            ])
            ->addColumn('confirmed_at', 'datetime', [
                'null' => true,
                'comment' => 'The date time when the password reset was successful',
                'after' => 'email_sent_at',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'The record creation date time',
                'after' => 'confirmed_at',
            ])
            ->addColumn('created_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record creator user id',
                'after' => 'created_at',
            ])
            ->addColumn('modified_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'The record last modification date time',
                'after' => 'created_by',
            ])
            ->addColumn('modified_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record last modifier user id',
                'after' => 'modified_at',
            ])
            ->addColumn('archived_at', 'datetime', [
                'null' => true,
                'comment' => 'The record archive date time',
                'after' => 'modified_by',
            ])
            ->addColumn('archived_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record archiver user id',
                'after' => 'archived_at',
            ])
            ->addIndex(['user_id'], [
                'name' => 'fk_password_reset_request_user_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('role', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('name', 'string', [
                'null' => false,
                'limit' => 80,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'The name of the role',
                'after' => 'id',
            ])
            ->addColumn('description', 'text', [
                'null' => false,
                'limit' => MysqlAdapter::TEXT_LONG,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'The description of usage for the role',
                'after' => 'name',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'The record creation date time',
                'after' => 'description',
            ])
            ->addColumn('created_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record creator user id',
                'after' => 'created_at',
            ])
            ->addColumn('modified_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'The record last modification date time',
                'after' => 'created_by',
            ])
            ->addColumn('modified_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record last modifier user id',
                'after' => 'modified_at',
            ])
            ->addColumn('archived_at', 'datetime', [
                'null' => true,
                'comment' => 'The record archive date time',
                'after' => 'modified_by',
            ])
            ->addColumn('archived_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record archiver user id',
                'after' => 'archived_at',
            ])
            ->create();
        $this->table('user', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('language_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The users preferred language',
                'after' => 'id',
            ])
            ->addColumn('username', 'string', [
                'null' => false,
                'limit' => 80,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'The username of the user (e.g. „d4rkmindz“)',
                'after' => 'language_id',
            ])
            ->addColumn('email', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'The email of the user (e.g. admin@your-domain.com)',
                'after' => 'username',
            ])
            ->addColumn('email_verified', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'email',
            ])
            ->addColumn('first_name', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'The first name of the user (e.g. „Björn“)',
                'after' => 'email_verified',
            ])
            ->addColumn('registration_method', 'string', [
                'null' => false,
                'default' => 'default',
                'limit' => 10,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'The sign up method.
Possible options:
- „google“ for google oauth login
- „default“ for username/password',
                'after' => 'first_name',
            ])
            ->addColumn('password', 'string', [
                'null' => true,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'The HASHED password of the user',
                'after' => 'registration_method',
            ])
            ->addColumn('last_name', 'string', [
                'null' => true,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'comment' => 'The last name of the user (e.g. „Pfoster“)',
                'after' => 'password',
            ])
            ->addColumn('last_login_at', 'datetime', [
                'null' => true,
                'comment' => 'The date time of the users last login',
                'after' => 'last_name',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'The record creation date time',
                'after' => 'last_login_at',
            ])
            ->addColumn('created_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record creator user id',
                'after' => 'created_at',
            ])
            ->addColumn('modified_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'The record last modification date time',
                'after' => 'created_by',
            ])
            ->addColumn('modified_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record last modifier user id',
                'after' => 'modified_at',
            ])
            ->addColumn('archived_at', 'datetime', [
                'null' => true,
                'comment' => 'The record archive date time',
                'after' => 'modified_by',
            ])
            ->addColumn('archived_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record archiver user id',
                'after' => 'archived_at',
            ])
            ->addIndex(['language_id'], [
                'name' => 'fk_user_language1_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('user_has_group', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
            ->addColumn('group_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'user_id',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'The record creation date time',
                'after' => 'group_id',
            ])
            ->addColumn('created_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record creator user id',
                'after' => 'created_at',
            ])
            ->addColumn('modified_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'The record last modification date time',
                'after' => 'created_by',
            ])
            ->addColumn('modified_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record last modifier user id',
                'after' => 'modified_at',
            ])
            ->addColumn('archived_at', 'datetime', [
                'null' => true,
                'comment' => 'The record archive date time',
                'after' => 'modified_by',
            ])
            ->addColumn('archived_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record archiver user id',
                'after' => 'archived_at',
            ])
            ->addIndex(['group_id'], [
                'name' => 'fk_user_has_group_group1_idx',
                'unique' => false,
            ])
            ->addIndex(['user_id'], [
                'name' => 'fk_user_has_group_user1_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('user_has_role', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
            ->addColumn('role_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'user_id',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'The record creation date time',
                'after' => 'role_id',
            ])
            ->addColumn('created_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record creator user id',
                'after' => 'created_at',
            ])
            ->addColumn('modified_at', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'The record last modification date time',
                'after' => 'created_by',
            ])
            ->addColumn('modified_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record last modifier user id',
                'after' => 'modified_at',
            ])
            ->addColumn('archived_at', 'datetime', [
                'null' => true,
                'comment' => 'The record archive date time',
                'after' => 'modified_by',
            ])
            ->addColumn('archived_by', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'The record archiver user id',
                'after' => 'archived_at',
            ])
            ->addIndex(['role_id'], [
                'name' => 'fk_user_has_role_role1_idx',
                'unique' => false,
            ])
            ->addIndex(['user_id'], [
                'name' => 'fk_user_has_role_user1_idx',
                'unique' => false,
            ])
            ->create();
    }
}
