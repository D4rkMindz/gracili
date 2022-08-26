<?php


namespace Test\Api\User\Role;

require_once __DIR__ . '/../../../../resources/seeds/UserSeed.php';
require_once __DIR__ . '/../../../../resources/seeds/ACLSeed.php';

use ACLSeed;
use ApiTester;
use App\Service\ID\HashID;
use App\Table\UserHasRoleTable;
use App\Type\Auth\Role;
use App\Type\HttpCode;
use Codeception\Example;
use UserSeed;

class UserRolesDeleteCest
{
    /**
     * Try to view users roles
     *
     * @dataProvider removeRoleProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToRemoveARoleFromAUser(ApiTester $I, Example $example)
    {
        $userId = $example->offsetGet('id');
        $roleId = $example->offsetGet('role');
        $I->amJWTAuthenticated(UserSeed::SECURITY_ADMIN);
        $I->sendDelete('/v1/users/' . HashID::encode($userId) . '/roles/' . HashID::encode($roleId));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonHasValue('$.message', 'Successfully removed role from user');
        $I->seeResponseJsonMatchesJsonPath('$.role');
        $I->seeResponseJsonHasValue('$.role.id', HashID::encode($roleId));

        $I->dontSeeInDatabaseExcludingArchived(UserHasRoleTable::getName(),
            ['user_id' => $userId, 'role_id' => $roleId]);
    }

    /**
     * Try to view users roles
     *
     * @dataProvider removeRoleUnauthorizedProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToAddRoleToAUserUnauthorized(ApiTester $I, Example $example)
    {
        $userId = $example->offsetGet('id');
        $user = $example->offsetGet('user');
        $roleId = $example->offsetGet('role');
        $I->amJWTAuthenticated($user);
        $I->sendDelete('/v1/users/' . HashID::encode($userId) . '/roles/' . HashID::encode($roleId));
        $I->seeNotAuthorized();
    }

    /**
     * Try to add a role to a user that already has that role assigned
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function tryToRemoveARoleFromAUserThatAlreadyDontHasRole(ApiTester $I)
    {
        $I->amJWTAuthenticated(UserSeed::SECURITY_ADMIN);

        $userId = UserSeed::USER_ID[UserSeed::ADMIN];
        $roleId = ACLSeed::ROLE_ID[Role::SECURITY_ADMIN];

        $I->dontSeeInDatabaseExcludingArchived(UserHasRoleTable::getName(),
            ['user_id' => $userId, 'role_id' => $roleId]);
        $I->sendDelete('/v1/users/' . HashID::encode($userId) . '/roles/' . HashID::encode($roleId));
        $I->seeValidationErrors('Please check your data', [
            [
                'field' => 'role',
                'message' => 'Role not assigned to user',
            ],
        ]);
        $I->dontSeeInDatabaseExcludingArchived(UserHasRoleTable::getName(),
            ['user_id' => $userId, 'role_id' => $roleId]);
    }

    /**
     * Add a role to a user data provider
     *
     * @return array|array[]
     */
    protected function removeRoleProvider(): array
    {
        return [
            UserSeed::SECURITY_ADMIN . ' removing ' . Role::ADMIN . ' from ' . UserSeed::ADMIN => [
                'id' => UserSeed::USER_ID[UserSeed::ADMIN],
                'role' => ACLSeed::ROLE_ID[Role::ADMIN],
            ],
            UserSeed::SECURITY_ADMIN . ' removing ' . Role::USER . ' from ' . UserSeed::USER => [
                'id' => UserSeed::USER_ID[UserSeed::USER],
                'role' => ACLSeed::ROLE_ID[Role::USER],
            ],
        ];
    }

    /**
     * Add a role to a user as unauthorized user data provider
     *
     * @return array
     */
    protected function removeRoleUnauthorizedProvider(): array
    {
        return [
            UserSeed::USER . ' trying to remove ' . Role::ADMIN . ' from ' . UserSeed::ADMIN . '\'s roles' => [
                'id' => UserSeed::USER_ID[UserSeed::ADMIN],
                'user' => UserSeed::USER,
                'role' => ACLSeed::ROLE_ID[Role::ADMIN],
            ],
            UserSeed::ADMIN . ' trying to remove ' . Role::SECURITY_ADMIN . ' from ' . UserSeed::SECURITY_ADMIN . '\'s roles' => [
                'id' => UserSeed::USER_ID[UserSeed::SECURITY_ADMIN],
                'user' => UserSeed::ADMIN,
                'role' => ACLSeed::ROLE_ID[Role::SECURITY_ADMIN],
            ],
        ];
    }
}
