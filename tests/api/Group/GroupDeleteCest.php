<?php


namespace Test\Api\Group;

require_once __DIR__ . '/../../../resources/seeds/UserSeed.php';
require_once __DIR__ . '/../../../resources/seeds/ACLSeed.php';

use ACLSeed;
use ApiTester;
use App\Service\ID\HashID;
use App\Table\GroupTable;
use App\Type\Auth\Group;
use App\Type\HttpCode;
use Codeception\Example;
use UserSeed;

class GroupDeleteCest
{
    /**
     * Try to delete group successfully
     *
     * @dataProvider groupDeleteProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToDeleteAGroup(ApiTester $I, Example $example)
    {
        $groupId = $example->offsetGet('id');
        $I->amJWTAuthenticated(UserSeed::SECURITY_ADMIN);
        $I->sendDelete('/v1/groups/' . HashID::encode($groupId));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonHasValue('$.message', 'Deleted group successfully');
        $I->seeResponseJsonMatchesJsonPath('$.group_id');
        $I->seeResponseJsonHasValue('$.group_id', HashID::encode($groupId));

        $groupHash = $I->grabDataFromResponseByJsonPath('$.group_id');

        $I->assertIsNotNumeric($groupHash); // expect to be hashed

        $I->dontSeeInDatabase(GroupTable::getName(), ['id' => $groupId]);
    }

    /**
     * Try to delete a group using a user that is not authorized
     *
     * @dataProvider groupDeleteUnauthorizedProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToDeleteAGroupUnauthorized(ApiTester $I, Example $example)
    {
        $user = $example->offsetGet('user');
        $groupId = $example->offsetGet('id');
        $I->amJWTAuthenticated($user);
        $I->sendDelete('/v1/groups/' . HashID::encode($groupId));
        $I->seeNotAuthorized();
        $I->seeInDatabaseExcludingArchived(GroupTable::getName(), ['id' => $groupId]);
    }

    /**
     * Try to archive a user twice
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function tryToDeleteAGroupTwice(ApiTester $I)
    {
        $user = UserSeed::SECURITY_ADMIN;
        $groupId = ACLSeed::GROUP_ID[Group::USER];
        $I->amJWTAuthenticated($user);
        $I->sendDelete('/v1/groups/' . HashID::encode($groupId));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonHasValue('$.message', 'Deleted group successfully');

        $I->dontSeeInDatabase(GroupTable::getName(), ['id' => $groupId]);

        $I->amJWTAuthenticated($user);
        $I->sendDelete('/v1/groups/' . HashID::encode($groupId));
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', false);
        $I->seeResponseJsonHasValue('$.message', 'Not found');
        $I->seeResponseJsonHasValue('$.error_type', 'not_found');
    }

    /**
     * User delete unauthorized data provider
     *
     * @return array[]
     */
    protected function groupDeleteUnauthorizedProvider(): array
    {
        return [
            UserSeed::USER . ' trying to delete ' . Group::SECURITY_ADMIN . ' (' . ACLSeed::GROUP_ID[Group::SECURITY_ADMIN] . ')' => [
                'user' => UserSeed::USER,
                'id' => ACLSeed::GROUP_ID[Group::SECURITY_ADMIN],
            ],
            UserSeed::USER . ' trying to delete ' . Group::ADMIN . ' (' . ACLSeed::GROUP_ID[Group::ADMIN] . ')' => [
                'user' => UserSeed::USER,
                'id' => ACLSeed::GROUP_ID[Group::ADMIN],
            ],
            UserSeed::USER . ' trying to delete ' . Group::SECURITY_ADMIN . ' (' . ACLSeed::GROUP_ID[Group::SECURITY_ADMIN] . ')' => [
                'user' => UserSeed::USER,
                'id' => ACLSeed::GROUP_ID[Group::SECURITY_ADMIN],
            ],
            UserSeed::ADMIN . ' trying to delete ' . Group::USER . ' (' . ACLSeed::GROUP_ID[Group::USER] . ')' => [
                'user' => UserSeed::ADMIN,
                'id' => ACLSeed::GROUP_ID[Group::USER],
            ],
            UserSeed::ADMIN . ' trying to delete ' . Group::ADMIN . ' (' . ACLSeed::GROUP_ID[Group::ADMIN] . ')' => [
                'user' => UserSeed::ADMIN,
                'id' => ACLSeed::GROUP_ID[Group::ADMIN],
            ],
            UserSeed::ADMIN . ' trying to delete ' . Group::SECURITY_ADMIN . ' (' . ACLSeed::GROUP_ID[Group::SECURITY_ADMIN] . ')' => [
                'user' => UserSeed::ADMIN,
                'id' => ACLSeed::GROUP_ID[Group::SECURITY_ADMIN],
            ],
        ];
    }

    /**
     * Delete group data provider
     *
     * @return array[]
     */
    protected function groupDeleteProvider(): array
    {
        return [
            'Delete group ' . Group::SECURITY_ADMIN . '(' . ACLSeed::GROUP_ID[Group::SECURITY_ADMIN] . ')' => ['id' => ACLSeed::GROUP_ID[Group::SECURITY_ADMIN]],
            'Delete group ' . Group::ADMIN . '(' . ACLSeed::GROUP_ID[Group::ADMIN] . ')' => ['id' => ACLSeed::GROUP_ID[Group::ADMIN]],
            'Delete group ' . Group::USER . '(' . ACLSeed::GROUP_ID[Group::USER] . ')' => ['id' => ACLSeed::GROUP_ID[Group::USER]],
        ];
    }
}
