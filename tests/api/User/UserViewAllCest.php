<?php


namespace Test\Api\User;

require_once __DIR__ . '/../../../resources/seeds/UserSeed.php';

use ApiTester;
use App\Type\HttpCode;
use UserSeed;

class UserViewAllCest
{
    /**
     * Testing view all
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function tryToViewAllUsers(ApiTester $I)
    {
        $I->amJWTAuthenticated(UserSeed::SECURITY_ADMIN);
        $I->sendGet('/v1/users');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonMatchesJsonPath('$.users');
        $response = $I->grabDataFromResponseByJsonPath('$')[0];
        $count = count($response['users']);
        $I->seeResponseJsonHasValue('$.message', 'Found ' . $count . ' users');
        $I->seeResponseJsonHasValue('$.count', $count);
        foreach ($response['users'] as $user) {
            $I->assertArrayNotHasKey('password', $user);

            $I->assertArrayHasKey('id', $user);
            $I->assertIsNotNumeric($user['id']); // expect to be hashed
            $I->assertArrayHasKey('username', $user);
            $I->assertArrayHasKey('first_name', $user);
            $I->assertArrayHasKey('last_name', $user);
            $I->assertArrayHasKey('email', $user);
            $I->assertArrayHasKey('last_login_at', $user);
            $I->assertArrayHasKey('language', $user);
            $I->assertArrayHasKey('id', $user['language']);
            $I->assertIsNotNumeric($user['language']['id']); // expect to be hashed
            $I->assertArrayHasKey('tag', $user['language']);
            $I->assertArrayHasKey('name', $user['language']);
            $I->assertArrayHasKey('english_name', $user['language']);
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
        $I->sendGet('/v1/users');
        $I->seeNotAuthorized();
    }
}
