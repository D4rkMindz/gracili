<?php


namespace Test\Api\Group;

require_once __DIR__ . '/../../../resources/seeds/UserSeed.php';
require_once __DIR__ . '/../../../resources/seeds/LanguageSeed.php';

use ApiTester;
use App\Table\GroupTable;
use App\Type\HttpCode;
use Codeception\Example;
use UserSeed;

class GroupCreateCest
{
    /**
     * Test creating a user
     *
     * @dataProvider groupCreateProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToCreateAGroup(ApiTester $I, Example $example)
    {
        $authenticatedUser = $example->offsetGet('authenticated_user');
        $groupData = $example->offsetGet('group');
        $verifyData = $example->offsetGet('verify');
        $I->amJWTAuthenticated($authenticatedUser);
        $I->sendPost('/v1/groups', $groupData);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonHasValue('$.message', 'Created group successfully');
        $I->seeResponseJsonMatchesJsonPath('$.group');

        $group = $I->grabDataFromResponseByJsonPath('$.group')[0];

        $I->assertArrayHasKey('id', $group);
        $I->assertIsNotNumeric($group['id']); // expect to be hashed

        $I->assertArrayHasKey('name', $group);
        $I->assertArrayHasKey('description', $group);

        // verify the data
        $I->seeResponseJsonHasValue('$.group.name', $verifyData['name']);
        $I->seeResponseJsonHasValue('$.group.description', $verifyData['description']);

        $I->seeInDatabaseExcludingArchived(GroupTable::getName(), $verifyData);
    }

    /**
     * Try to test unauthorized
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function tryToCreateAGroupUnauthorized(ApiTester $I)
    {
        $user = UserSeed::ADMIN;
        $I->amJWTAuthenticated($user);
        $I->sendPost('/v1/groups', [
            'name' => 'group.test',
            'description' => 'testing purposes only',
        ]);
        $I->seeNotAuthorized();
    }

    /**
     * Data provider for creating groups
     *
     * @return array[]
     */
    protected function groupCreateProvider(): array
    {
        return [
            UserSeed::SECURITY_ADMIN . ' creating a group' => [
                'authenticated_user' => UserSeed::SECURITY_ADMIN,
                'group' => [
                    'name' => 'group.test',
                    'description' => 'testing purposes only',
                ],
                'verify' => [
                    'name' => 'group.test',
                    'description' => 'testing purposes only',
                ],
            ],
        ];
    }
}
