<?php


namespace Test\Api\User\Role;

require_once __DIR__ . '/../../../../resources/seeds/UserSeed.php';
require_once __DIR__ . '/../../../../resources/seeds/ACLSeed.php';

use ACLSeed;
use ApiTester;
use App\Service\ID\HashID;
use App\Type\Auth\Role;
use App\Type\HttpCode;
use Codeception\Example;
use UserSeed;

/**
 * Class UserRolesViewAllCest
 */
class UserRolesViewAllCest
{
    /**
     * Try to view users roles
     *
     * @dataProvider viewAllRolesProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToViewAllRolesOfAUser(ApiTester $I, Example $example)
    {
        $userId = $example->offsetGet('id');
        $user = $example->offsetGet('user');
        $roles = $example->offsetGet('roles');
        $indirect = $example->offsetGet('indirect');
        $I->amJWTAuthenticated($user);
        $I->sendGet('/v1/users/' . HashID::encode($userId) . '/roles');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonMatchesJsonPath('$.roles');
        $I->seeResponseJsonMatchesJsonPath('$.indirect');
        $response = $I->grabDataFromResponseByJsonPath('$')[0];
        $count = count($response['roles']);
        $I->seeResponseJsonHasValue('$.message', 'Found ' . $count . ' roles');
        $I->seeResponseJsonHasValue('$.count', $count);
        foreach ($roles as $role) {
            $I->seeResponseJsonHasValue('$.roles[*].id', $role['id']);
        }
        foreach ($indirect as $indirectRole) {
            $I->seeResponseJsonHasValue('$.indirect[*].id', $indirectRole['id']);
        }
    }

    /**
     * Try to view users roles
     *
     * @dataProvider viewAllRolesUnauthorizedProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToViewAllRolesOfAUserUnauthorized(ApiTester $I, Example $example)
    {
        $userId = $example->offsetGet('id');
        $user = $example->offsetGet('user');
        $I->amJWTAuthenticated($user);
        $I->sendGet('/v1/users/' . HashID::encode($userId) . '/roles');
        $I->seeNotAuthorized();
    }

    /**
     * View all roles data provider
     *
     * @return array|array[]
     */
    public function viewAllRolesProvider(): array
    {
        $data = [
            UserSeed::SECURITY_ADMIN . ' viewing ' . UserSeed::USER => [
                'id' => UserSeed::USER_ID[UserSeed::USER],
                'user' => UserSeed::SECURITY_ADMIN,
                'roles' => [],
                'indirect' => [
                    ['id' => HashID::encode(ACLSeed::ROLE_ID[Role::USER])],
                ],
            ],
            UserSeed::SECURITY_ADMIN . ' viewing ' . UserSeed::SECURITY_ADMIN => [
                'id' => UserSeed::USER_ID[UserSeed::SECURITY_ADMIN],
                'user' => UserSeed::SECURITY_ADMIN,
                'roles' => [],
                'indirect' => [],
            ],
            // a user should see their own roles
            UserSeed::USER . ' viewing ' . UserSeed::USER => [
                'id' => UserSeed::USER_ID[UserSeed::USER],
                'user' => UserSeed::USER,
                'roles' => [],
                'indirect' => [
                    ['id' => HashID::encode(ACLSeed::ROLE_ID[Role::USER])],
                ],
            ],
        ];

        $roles = ACLSeed::ROLE_ID;
        foreach ($roles as $key => $roleId) {
            $roles[] = ['id' => HashID::encode($roleId)];
            unset($roles[$key]);
        }
        $data[UserSeed::SECURITY_ADMIN . ' viewing ' . UserSeed::SECURITY_ADMIN]['indirect'] = $roles;

        return $data;
    }

    /**
     * View all roles unauthorized data provider
     *
     * @return array
     */
    public function viewAllRolesUnauthorizedProvider(): array
    {
        return [
            UserSeed::USER . ' trying to view ' . UserSeed::ADMIN . '\'s roles' => [
                'id' => UserSeed::USER_ID[UserSeed::ADMIN],
                'user' => UserSeed::USER,
            ],
            UserSeed::ADMIN . ' trying to view ' . UserSeed::USER . '\'s roles' => [
                'id' => UserSeed::USER_ID[UserSeed::USER],
                'user' => UserSeed::ADMIN,
            ],
        ];
    }
}
