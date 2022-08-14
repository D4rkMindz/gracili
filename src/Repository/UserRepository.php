<?php

namespace App\Repository;

use App\Exception\RecordNotFoundException;
use App\Table\UserTable;
use Moment\Moment;

/**
 * Class UserRepository.
 */
class UserRepository extends AppRepository
{
    private UserTable $userTable;

    /**
     * Constructor
     *
     * @param UserTable $userTable
     */
    public function __construct(UserTable $userTable)
    {
        $this->userTable = $userTable;
    }

    /**
     * Get a password of a single user.
     *
     * @param int $userId
     *
     * @return string
     * @throws RecordNotFoundException
     */
    public function getPassword(int $userId): string
    {
        $query = $this->userTable->newSelect();
        $query->select(['password'])
            ->where(['id' => $userId]);
        $user = $query->execute()->fetch('assoc');

        if (!empty($user)) {
            return $user['password'];
        }

        throw new RecordNotFoundException(__('User not found'), (string)$userId);
    }

    /**
     * Get the last login of a user
     *
     * @param int $userId
     *
     * @return Moment|null
     * @throws RecordNotFoundException
     */
    public function getLastLogin(int $userId): ?Moment
    {
        $query = $this->userTable->newSelect();
        $query->select(['last_login_at'])
            ->where(['id' => $userId]);
        $result = $query->execute()->fetch('assoc');
        if (!empty($result)) {
            $lastLogin = $result['last_login_at'];
            if (!empty($lastLogin)) {
                return new Moment($lastLogin);
            }

            return null;
        }

        throw new RecordNotFoundException(__('User not foud'), 'user id = ' . $userId);
    }

    /**
     * Get a users language tag (e.g. "de_CH")
     *
     * @param int $userId
     *
     * @return string
     * @throws RecordNotFoundException
     */
    public function getLanguageTag(int $userId): string
    {
        $query = $this->userTable->newSelect();
        $query->select(['language.tag'])
            ->join([
                'language' => [
                    'table' => 'language',
                    'type' => 'INNER',
                    'conditions' => 'user.language_id = language.id',
                ],
            ])
            ->where(['user.id' => $userId]);

        $result = $query->execute()->fetch('assoc');
        if (!empty($result)) {
            return $result['tag'];
        }

        throw new RecordNotFoundException(__('Language not found'), 'user id = ' . $userId);
    }

    /**
     * Get a user by its ID
     *
     * @param int $userId
     *
     * @return mixed
     * @throws RecordNotFoundException
     */
    public function getUserById(int $userId)
    {
        $query = $this->userTable->newSelect();
        $query->select([
            'id',
            'username',
            'password',
            'email',
            'first_name',
            'last_name',
            'last_login_at',
        ])
            ->where(['id' => $userId]);
        $user = $query->execute()->fetch('assoc');

        if (!empty($user)) {
            return $user;
        }

        throw new RecordNotFoundException(__('User not found'), (string)$userId);
    }

    /**
     * Get a user's id by a field
     *
     * @param string      $field
     * @param string $value
     *
     * @return int
     * @throws RecordNotFoundException
     */
    public function getIdBy(string $field, string $value): int
    {
        $query = $this->userTable->newSelect();
        $query->select(['id'])->where([$field => $value]);

        $user = $query->execute()->fetch('assoc');

        if (!empty($user)) {
            return (int)$user['id'];
        }

        throw new RecordNotFoundException(__(
            'Could not get user by {field} with value {value}',
            ['field' => $field, 'value' => $value]
        ), 'Field ' . $field . ' by value ' . $value);
    }

    /**
     * Check if a username exists.
     *
     * @param string|null $username
     *
     * @return bool true if someone already registered the given username
     */
    public function existsUsername(?string $username): bool
    {
        return $this->userTable->exist(['username' => $username]);
    }

    /**
     * Check if email is already registered.
     *
     * @param string|null $email
     * @param int|null    $excludeUserId
     *
     * @return bool true if someone already registered the given email
     */
    public function existsEmail(?string $email, ?int $excludeUserId): bool
    {
        $search = ['email' => $email];
        if (!empty($excludeUserId)) {
            $search['id !='] = $excludeUserId;
        }

        return $this->userTable->exist($search);
    }

    /**
     * Create the user
     *
     * @param string $username
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $password
     * @param int    $executorId
     *
     * @return int
     */
    public function createUser(
        string $username,
        string $email,
        string $firstName,
        string $lastName,
        string $password,
        int $executorId
    ): int {
        $user = [
            'username' => $username,
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ];

        return (int)$this->userTable->insert($user, $executorId)->lastInsertId();
    }

    /**
     * Set the last login date of a user
     *
     * @param int    $userId
     * @param string $datetime
     * @param int    $executorId
     *
     * @return bool
     */
    public function setLastLogin(int $userId, string $datetime, int $executorId): bool
    {
        return $this->userTable->update(['last_login_at' => $datetime], ['id' => $userId], $executorId);
    }

    /**
     * Update a user.
     *
     * @param int         $userId
     * @param int         $executorId
     * @param string|null $username
     * @param string|null $email
     * @param string|null $firstName
     * @param string|null $lastName
     * @param string|null $password
     *
     * @return bool
     */
    public function modifyUser(
        int $userId,
        int $executorId,
        ?string $username,
        ?string $email,
        ?string $firstName,
        ?string $lastName,
        ?string $password,
    ): bool {
        $row = [];

        if (!empty($username)) {
            $row['username'] = $username;
        }
        if (!empty($firstName)) {
            $row['first_name'] = $firstName;
        }
        if (!empty($lastName)) {
            $row['last_name'] = $lastName;
        }
        if (!empty($email)) {
            $row['email'] = $email;
        }
        if (!empty($password)) {
            $row['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if (!empty($row)) {
            return $this->userTable->update($row, ['id' => $userId], $executorId);
        }

        return false;
    }

    /**
     * Archive a user.
     *
     * @param int $userId
     * @param int $executorId
     *
     * @return bool
     */
    public function archiveUser(int $userId, int $executorId): bool
    {
        return $this->userTable->archive($userId, $executorId);
    }
}
