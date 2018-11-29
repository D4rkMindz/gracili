<?php

namespace App\Repository;

use App\Table\UserTable;
use Interop\Container\Exception\ContainerException;
use Slim\Container;

/**
 * Class UserRepository
 */
class UserRepository extends AppRepository
{
    /**
     * @var UserTable $userTable
     */
    private $userTable;

    /**
     * UserRepository constructor.
     *
     * @param Container $container
     * @throws ContainerException
     */
    public function __construct(Container $container)
    {
        $this->userTable = $container->get(UserTable::class);
    }

    /**
     * Get all users.
     *
     * @return array
     */
    public function getAllUsers()
    {
        $fields = [
            'id',
            'username',
            'email',
            'meta/created/at' => 'created_at',
            'meta/created/by' => 'created_by',
            'meta/modified_at' => 'modified_at',
            'meta/modified_by' => 'modified_by',
            'meta/archived_at' => 'archived_at',
            'meta/archived_by' => 'archived_by',
        ];

        $query = $this->userTable->newSelect();
        $query->select($fields);
        $users = $query->execute()->fetchAll('assoc');

        return $this->format($users);
    }

    /**
     * Get a single user.
     *
     * @param string $userId
     * @return array
     */
    public function getUser(string $userId)
    {
        $fields = [
            'id',
            'username',
            'email',
            'password',
            'meta.created_at' => 'created_at',
            'meta.created_by' => 'created_by',
            'meta.modified_at' => 'modified_at',
            'meta.modified_by' => 'modified_by',
            'meta.archived_at' => 'archived_at',
            'meta.archived_by' => 'archived_by',
        ];

        $query = $this->userTable->newSelect();
        $query->select($fields)->where(['id' => $userId]);
        $user = $query->execute()->fetchAll('assoc');

        return $user;
    }

    /**
     * Create a user.
     *
     * @param string $username
     * @param string $email
     * @param string $password
     * @return string The ID of the user
     */
    public function createUser(string $username, string $email, string $password): string
    {
        $row = [
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'created_by' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        return (string)$this->userTable->insert($row)->lastInsertId();
    }

    /**
     * Update a user.
     *
     * @param string $userId
     * @param string $executorId
     * @param null|string $username
     * @param null|string $email
     * @param null|string $password
     * @return bool
     */
    public function updateUser(string $userId, string $executorId, ?string $username, ?string $email, ?string $password): bool
    {
        $row = [
            'modified_by' => $executorId,
            'modified_at' => date('Y-m-d H:i:s'),
        ];

        if (!empty($username)) {
            $row['username'] = $username;
        }
        if (!empty($email)) {
            $row['email'] = $email;
        }
        if (!empty($password)) {
            $row['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        return $this->userTable->update($row, ['id' => $userId]);
    }

    /**
     * Archive a user
     *
     * @param string $userId
     * @param string $executorId
     * @return bool
     */
    public function archiveUser(string $userId, string $executorId): bool
    {
        return $this->userTable->archive($userId, $executorId);
    }

    /**
     * Check if a username exists.
     *
     * @param string $username
     * @return bool true if someone already registered the given username
     */
    public function existsUsername(string $username): bool
    {
        return $this->userTable->exist(['username' => $username]);
    }

    /**
     * Check if email is already registered.
     *
     * @param string $email
     * @return bool true if someone already registered the given email
     */
    public function existsEmail(string $email): bool
    {
        return $this->userTable->exist(['email' => $email]);
    }
}
