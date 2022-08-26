<?php


namespace Test\Api\Role;

require_once __DIR__ . '/../../../resources/seeds/UserSeed.php';
require_once __DIR__ . '/../../../resources/seeds/ACLSeed.php';

use ACLSeed;
use ApiTester;
use App\Service\ID\HashID;
use App\Type\Auth\Role;
use App\Type\HttpCode;
use Codeception\Example;
use UserSeed;

class RoleViewCest
{
    /**
     * Testing view all
     *
     * @dataProvider viewRoleProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToViewARole(ApiTester $I, Example $example)
    {
        $authenticatedUser = $example->offsetGet('authenticated_user');
        $roleId = $example->offsetGet('id');
        $I->amJWTAuthenticated($authenticatedUser);
        $I->sendGet('/v1/roles/' . HashID::encode($roleId));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonMatchesJsonPath('$.role');
        $role = $I->grabDataFromResponseByJsonPath('$.role')[0];

        $I->assertArrayHasKey('id', $role);
        $I->assertIsNotNumeric($role['id']); // expect to be hashed
        $I->assertSame(HashID::encode($roleId), $role['id']);

        $I->assertArrayHasKey('name', $role);
        $I->assertArrayHasKey('description', $role);
    }

    /**
     * Test not authorized
     *
     * @dataProvider viewUserUnauthorizedProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    protected function tryToViewUnauthorized(ApiTester $I, Example $example)
    {
        $I->amJWTAuthenticated($example->offsetGet('authenticated_user'));
        $I->sendGet('/v1/roles/' . $example->offsetGet('id'));
        $I->seeNotAuthorized();
    }

    /**
     * Data provider for viewing users successfully
     *
     * @return array[]
     */
    protected function viewRoleProvider(): array
    {
        return [
            UserSeed::SECURITY_ADMIN . ' viewing ' . Role::SECURITY_ADMIN => [
                'authenticated_user' => UserSeed::SECURITY_ADMIN,
                'id' => ACLSeed::ROLE_ID[Role::SECURITY_ADMIN],
            ],
            UserSeed::SECURITY_ADMIN . ' viewing ' . Role::ADMIN => [
                'authenticated_user' => UserSeed::SECURITY_ADMIN,
                'id' => ACLSeed::ROLE_ID[Role::ADMIN],
            ],
            UserSeed::SECURITY_ADMIN . ' viewing ' . Role::USER => [
                'authenticated_user' => UserSeed::SECURITY_ADMIN,
                'id' => ACLSeed::ROLE_ID[Role::ADMIN],
            ],
        ];
    }

    /**
     * Data provider for viewing user data that should not be allowed
     *
     * @return array[]
     */
    protected function viewUserUnauthorizedProvider(): array
    {
        return [
            // Viewing another user as security_admin MUST be possible
            UserSeed::USER . ' viewing ' . Role::ADMIN => [
                'authenticated_user' => UserSeed::USER,
                'id' => ACLSeed::ROLE_ID[Role::ADMIN],
            ],
            UserSeed::ADMIN . ' viewing ' . Role::ADMIN => [
                'authenticated_user' => UserSeed::ADMIN,
                'id' => ACLSeed::ROLE_ID[Role::ADMIN],
            ],
            UserSeed::USER . ' viewing ' . Role::SECURITY_ADMIN => [
                'authenticated_user' => UserSeed::USER,
                'id' => ACLSeed::ROLE_ID[Role::SECURITY_ADMIN],
            ],
        ];
    }
}
