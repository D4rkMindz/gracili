<?php

namespace App\Repository;

use App\Exception\RecordNotFoundException;
use App\Table\AppTable;
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
     * Get the default login method (based on registration)
     *
     * The user signs up either using username/password or an OAuth2.0 provider
     * By using an OAuth2.0 provider for regisitration, the default (username/password) login is not available
     * Yet, the default method could still by used (in combination with oauth), if the user sets a password
     *
     * @param int $userId
     *
     * @return string
     * @throws RecordNotFoundException
     */
    public function getDefaultLoginMethodForUser(int $userId): string
    {
        $query = $this->userTable->newSelect();
        $query->select(['registration_method'])
            ->where(['id' => $userId]);
        $result = $query->execute()->fetch('assoc');

        if (!empty($result)) {
            return $result['registration_method'];
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
     * @return array
     * @throws RecordNotFoundException
     */
    public function getUserById(int $userId): array
    {
        $query = $this->userTable->newSelect();
        $query->select([
            'id' => 'user.id',
            'username' => 'user.username',
            'email' => 'user.email',
            'first_name' => 'user.first_name',
            'last_name' => 'user.last_name',
            'last_login_at' => 'user.last_login_at',
            'language-id' => 'language.id',
            'language-tag' => 'language.tag',
            'language-name' => 'language.name',
            'language-english_name' => 'language.english_name',
        ])
            ->join([
                'language' => [
                    'table' => 'language',
                    'type' => 'INNER',
                    'conditions' => 'user.language_id = language.id',
                ],
            ])
            ->where(['user.id' => $userId]);
        $user = $query->execute()->fetch('assoc');

        if (!empty($user)) {
            return AppTable::recursify($user);
        }

        throw new RecordNotFoundException(__('User not found'), (string)$userId);
    }

    /**
     * Get all users
     *
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array
     * @throws RecordNotFoundException
     */
    public function getAllUsers(?int $limit = null, ?int $offset = null): array
    {
        $query = $this->userTable->newSelect();
        $query->select([
            'id' => 'user.id',
            'username' => 'user.username',
            'email' => 'user.email',
            'first_name' => 'user.first_name',
            'last_name' => 'user.last_name',
            'last_login_at' => 'user.last_login_at',
            'language-id' => 'language.id',
            'language-tag' => 'language.tag',
            'language-name' => 'language.name',
            'language-english_name' => 'language.english_name',
        ])
            ->join([
                'language' => [
                    'table' => 'language',
                    'type' => 'INNER',
                    'conditions' => 'user.language_id = language.id',
                ],
            ]);
        if ($limit !== null) {
            $query->limit($limit);
        }

        if ($offset !== null) {
            $query->offset($offset);
        }

        $result = $query->execute()->fetchAll('assoc');

        if (!empty($result)) {
            return AppTable::recursify($result);
        }

        throw new RecordNotFoundException(__('No users found'), 'count = ' . $limit . ' offset = ' . $offset);
    }

    /**
     * Get a user's id by a field
     *
     * @param string $field
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
    public function existsEmail(?string $email, ?int $excludeUserId = null): bool
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
     * @param int         $languageId
     * @param string      $username
     * @param string      $email
     * @param string      $password
     * @param string      $firstName
     * @param string|null $lastName
     * @param string|null $registrationMethod
     * @param bool        $emailVerified
     * @param int         $executorId
     *
     * @return int
     */
    public function createUser(
        int $languageId,
        string $username,
        string $email,
        string $password,
        string $firstName,
        ?string $lastName,
        ?string $registrationMethod,
        bool $emailVerified = false,
        int $executorId = 0
    ): int {
        $user = [
            'language_id' => $languageId,
            'username' => $username,
            'email' => $email,
            'email_verified' => $emailVerified,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'registration_method' => $registrationMethod,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ];

        $userId = (int)$this->userTable->insert($user, $executorId, ['email_verified' => 'boolean'])->lastInsertId();
        if ($executorId === 0) {
            $this->userTable->update([], ['id' => $userId], $userId);
        }

        return $userId;
    }

    /**
     * Set the last login date of a user
     *
     * @param int      $userId
     * @param string   $datetime
     * @param int|null $executorId
     *
     * @return bool
     */
    public function setLastLogin(int $userId, string $datetime, ?int $executorId = 0): bool
    {
        return $this->userTable->update(['last_login_at' => $datetime], ['id' => $userId], $executorId);
    }

    /**
     * Update a user.
     *
     * @param int         $userId
     * @param int         $executorId
     * @param int|null    $languageId
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
        ?int $languageId = null,
        ?string $username = null,
        ?string $email = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $password = null,
    ): bool {
        $row = [];

        if (!empty($languageId)) {
            $row['language_id'] = $languageId;
        }
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
            $row['email_verified'] = false;
        }
        if (!empty($password)) {
            $row['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if (!empty($row)) {
            return $this->userTable->update($row, ['id' => $userId], $executorId, ['email_verified' => 'boolean']);
        }

        return false;
    }

    /**
     * Archive a user.
     *
     * @param int      $userId
     * @param int|null $executorId
     *
     * @return bool
     */
    public function archiveUser(int $userId, ?int $executorId = 0): bool
    {
        return $this->userTable->archive($userId, $executorId);
    }

    /**
     * Delete a user
     *
     * HANDLE WITH CARE
     *
     * @param int $userId
     *
     * @return bool
     */
    public function deleteUser(int $userId): bool
    {
        return $this->userTable->delete($userId);
    }
}
