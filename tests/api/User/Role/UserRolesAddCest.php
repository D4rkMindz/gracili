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

class UserRolesAddCest
{
    /**
     * Try to view users roles
     *
     * @dataProvider addRoleProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToAddRoleToAUser(ApiTester $I, Example $example)
    {
        $userId = $example->offsetGet('id');
        $user = $example->offsetGet('user');
        $roleId = $example->offsetGet('role');
        $I->amJWTAuthenticated($user);
        $I->sendPost('/v1/users/' . HashID::encode($userId) . '/roles/' . HashID::encode($roleId));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonHasValue('$.message', 'Successfully assigned role to user');
        $I->seeResponseJsonMatchesJsonPath('$.role');
        $I->seeResponseJsonHasValue('$.role.id', HashID::encode($roleId));

        $I->seeInDatabaseExcludingArchived(UserHasRoleTable::getName(), ['user_id' => $userId, 'role_id' => $roleId]);
    }

    /**
     * Try to view users roles
     *
     * @dataProvider addRoleUnauthorizedProvider
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
        $I->sendPost('/v1/users/' . HashID::encode($userId) . '/roles/' . HashID::encode($roleId));
        $I->seeNotAuthorized();
    }

    /**
     * Try to add a role to a user that already has that role assigned
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function tryToAddRoleToAUserThatAlreadyHasRole(ApiTester $I)
    {
        $I->amJWTAuthenticated(UserSeed::SECURITY_ADMIN);

        $userId = UserSeed::USER_ID[UserSeed::ADMIN];
        $roleId = ACLSeed::ROLE_ID[Role::ADMIN];

        $I->seeInDatabaseExcludingArchived(UserHasRoleTable::getName(), ['user_id' => $userId, 'role_id' => $roleId]);
        $I->sendPost('/v1/users/' . HashID::encode($userId) . '/roles/' . HashID::encode($roleId));
        $I->seeValidationErrors('Please check your data', [
            [
                'field' => 'role',
                'message' => 'Role already assigned',
            ],
        ]);
        $I->seeInDatabaseExcludingArchived(UserHasRoleTable::getName(), ['user_id' => $userId, 'role_id' => $roleId]);
    }

    /**
     * Add a role to a user data provider
     *
     * @return array|array[]
     */
    protected function addRoleProvider(): array
    {
        return [
            UserSeed::SECURITY_ADMIN . ' adding ' . Role::ADMIN . ' to ' . UserSeed::USER => [
                'user' => UserSeed::SECURITY_ADMIN,
                'role' => ACLSeed::ROLE_ID[Role::ADMIN],
                'id' => UserSeed::USER_ID[UserSeed::USER],
            ],
            UserSeed::SECURITY_ADMIN . ' adding ' . Role::USER . ' to ' . UserSeed::ADMIN => [
                'user' => UserSeed::SECURITY_ADMIN,
                'role' => ACLSeed::ROLE_ID[Role::USER],
                'id' => UserSeed::USER_ID[UserSeed::ADMIN],
            ],
        ];
    }

    /**
     * Add a role to a user as unauthorized user data provider
     *
     * @return array
     */
    protected function addRoleUnauthorizedProvider(): array
    {
        return [
            UserSeed::USER . ' trying to add ' . Role::ADMIN . ' to ' . UserSeed::USER . '\'s roles' => [
                'user' => UserSeed::USER,
                'role' => ACLSeed::ROLE_ID[Role::ADMIN],
                'id' => UserSeed::USER_ID[UserSeed::USER],
            ],
            UserSeed::ADMIN . ' trying to add ' . Role::SECURITY_ADMIN . ' to ' . UserSeed::USER . '\'s roles' => [
                'user' => UserSeed::ADMIN,
                'role' => ACLSeed::ROLE_ID[Role::SECURITY_ADMIN],
                'id' => UserSeed::USER_ID[UserSeed::ADMIN],
            ],
        ];
    }
}
