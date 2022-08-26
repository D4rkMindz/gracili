<?php


namespace Test\Api\Role;

require_once __DIR__ . '/../../../resources/seeds/UserSeed.php';

use ApiTester;
use App\Type\HttpCode;
use UserSeed;

class RoleViewAllCest
{
    /**
     * Testing view all
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function tryToViewAllRoles(ApiTester $I)
    {
        $I->amJWTAuthenticated(UserSeed::SECURITY_ADMIN);
        $I->sendGet('/v1/roles');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonMatchesJsonPath('$.roles');
        $response = $I->grabDataFromResponseByJsonPath('$')[0];
        $count = count($response['roles']);
        $I->seeResponseJsonHasValue('$.message', 'Found ' . $count . ' roles');
        $I->seeResponseJsonHasValue('$.count', $count);
        foreach ($response['roles'] as $role) {
            $I->assertArrayHasKey('id', $role);
            $I->assertIsNotNumeric($role['id']); // expect to be hashed
            $I->assertArrayHasKey('name', $role);
            $I->assertArrayHasKey('description', $role);
        }
    }

    /**
     * Test not authorized
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function tryToViewAllUnauthorized(ApiTester $I)
    {
        $I->amJWTAuthenticated(UserSeed::USER);
        $I->sendGet('/v1/roles');
        $I->seeNotAuthorized();
    }
}
