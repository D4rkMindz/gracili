<?php


namespace Test\Api\Group;

require_once __DIR__ . '/../../../resources/seeds/UserSeed.php';
require_once __DIR__ . '/../../../resources/seeds/ACLSeed.php';

use ACLSeed;
use ApiTester;
use App\Service\ID\HashID;
use App\Type\Auth\Group;
use App\Type\HttpCode;
use Codeception\Example;
use UserSeed;

class GroupViewCest
{
    /**
     * Testing view all
     *
     * @dataProvider viewGroupProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToViewAGroup(ApiTester $I, Example $example)
    {
        $authenticatedUser = $example->offsetGet('authenticated_user');
        $groupId = $example->offsetGet('id');
        $I->amJWTAuthenticated($authenticatedUser);
        $I->sendGet('/v1/groups/' . HashID::encode($groupId));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonMatchesJsonPath('$.group');
        $group = $I->grabDataFromResponseByJsonPath('$.group')[0];

        $I->assertArrayHasKey('id', $group);
        $I->assertIsNotNumeric($group['id']); // expect to be hashed
        $I->assertSame(HashID::encode($groupId), $group['id']);

        $I->assertArrayHasKey('name', $group);
        $I->assertArrayHasKey('description', $group);
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
        $I->sendGet('/v1/groups/' . $example->offsetGet('id'));
        $I->seeNotAuthorized();
    }

    /**
     * Data provider for viewing users successfully
     *
     * @return array[]
     */
    protected function viewGroupProvider(): array
    {
        return [
            UserSeed::SECURITY_ADMIN . ' viewing ' . Group::SECURITY_ADMIN => [
                'authenticated_user' => UserSeed::SECURITY_ADMIN,
                'id' => ACLSeed::GROUP_ID[Group::SECURITY_ADMIN],
            ],
            UserSeed::SECURITY_ADMIN . ' viewing ' . Group::ADMIN => [
                'authenticated_user' => UserSeed::SECURITY_ADMIN,
                'id' => ACLSeed::GROUP_ID[Group::ADMIN],
            ],
            UserSeed::SECURITY_ADMIN . ' viewing ' . Group::USER => [
                'authenticated_user' => UserSeed::SECURITY_ADMIN,
                'id' => ACLSeed::GROUP_ID[Group::ADMIN],
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
            UserSeed::USER . ' viewing ' . Group::ADMIN => [
                'authenticated_user' => UserSeed::USER,
                'id' => ACLSeed::GROUP_ID[Group::ADMIN],
            ],
            UserSeed::ADMIN . ' viewing ' . Group::ADMIN => [
                'authenticated_user' => UserSeed::ADMIN,
                'id' => ACLSeed::GROUP_ID[Group::ADMIN],
            ],
            UserSeed::USER . ' viewing ' . Group::SECURITY_ADMIN => [
                'authenticated_user' => UserSeed::USER,
                'id' => ACLSeed::GROUP_ID[Group::SECURITY_ADMIN],
            ],
        ];
    }
}
