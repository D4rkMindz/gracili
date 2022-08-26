<?php


namespace Test\Api\Group;

require_once __DIR__ . '/../../../resources/seeds/UserSeed.php';

use ApiTester;
use App\Type\HttpCode;
use UserSeed;

class GroupViewAllCest
{
    /**
     * Testing view all
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function tryToViewAllGroups(ApiTester $I)
    {
        $I->amJWTAuthenticated(UserSeed::SECURITY_ADMIN);
        $I->sendGet('/v1/groups');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonMatchesJsonPath('$.groups');
        $response = $I->grabDataFromResponseByJsonPath('$')[0];
        $count = count($response['groups']);
        $I->seeResponseJsonHasValue('$.message', 'Found ' . $count . ' groups');
        $I->seeResponseJsonHasValue('$.count', $count);
        foreach ($response['groups'] as $group) {
            $I->assertArrayHasKey('id', $group);
            $I->assertIsNotNumeric($group['id']); // expect to be hashed
            $I->assertArrayHasKey('name', $group);
            $I->assertArrayHasKey('description', $group);
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
        $I->sendGet('/v1/groups');
        $I->seeNotAuthorized();
    }
}
