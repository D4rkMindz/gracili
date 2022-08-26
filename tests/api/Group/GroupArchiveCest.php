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
use Moment\Moment;
use UserSeed;

class GroupArchiveCest
{
    /**
     * Try to archive a user successfully
     *
     * @dataProvider archiveGroupProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToArchiveGroup(ApiTester $I, Example $example)
    {
        $groupId = $example->offsetGet('id');
        $user = $example->offsetGet('user');
        $I->amJWTAuthenticated($user);
        $I->sendPatch('/v1/groups/' . HashID::encode($groupId));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonHasValue('$.message', 'Archived group successfully');
        $I->seeResponseJsonMatchesJsonPath('$.group_id');
        $I->seeResponseJsonHasValue('$.group_id', HashID::encode($groupId));

        $userHash = $I->grabDataFromResponseByJsonPath('$.user_id');

        $I->assertIsNotNumeric($userHash); // expect to be hashed

        $I->seeInDatabase(GroupTable::getName(), [
            'id' => $groupId,
            'archived_by' => UserSeed::USER_ID[$user],
            'archived_at >=' => (new Moment())->subtractMinutes(5)->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Try to archive a user unsuccessfully
     *
     * @dataProvider archiveGroupUnsuccessfullyProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToArchiveGroupUnsuccessfully(ApiTester $I, Example $example)
    {
        $groupId = $example->offsetGet('id');
        $user = $example->offsetGet('user');
        $I->amJWTAuthenticated($user);
        $I->sendPatch('/v1/groups/' . HashID::encode($groupId));
        $I->seeNotAuthorized();
    }

    /**
     * Try to archive a user twice
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function tryToArchiveGroupTwice(ApiTester $I)
    {
        $user = UserSeed::SECURITY_ADMIN;
        $groupId = ACLSeed::GROUP_ID[Group::USER];
        $I->amJWTAuthenticated($user);
        $I->sendPatch('/v1/groups/' . HashID::encode($groupId));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonHasValue('$.message', 'Archived group successfully');

        $I->seeInDatabase(GroupTable::getName(), [
            'id' => $groupId,
            'archived_by' => UserSeed::USER_ID[$user],
            'archived_at >=' => (new Moment())->subtractMinutes(5)->format('Y-m-d H:i:s'),
        ]);

        $I->amJWTAuthenticated($user);
        $I->sendPatch('/v1/groups/' . HashID::encode($groupId));
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', false);
        $I->seeResponseJsonHasValue('$.message', 'Not found');
        $I->seeResponseJsonHasValue('$.error_type', 'not_found');
    }

    /**
     * A user must not appear in the list after archivation
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function tryToArchiveGroupAndThenNotSeeItAnymore(ApiTester $I)
    {
        $user = UserSeed::SECURITY_ADMIN;
        $groupId = ACLSeed::GROUP_ID[Group::USER];
        $I->amJWTAuthenticated($user);
        $I->sendPatch('/v1/groups/' . HashID::encode($groupId));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonHasValue('$.message', 'Archived group successfully');

        $I->seeInDatabase(GroupTable::getName(), [
            'id' => $groupId,
            'archived_by' => UserSeed::USER_ID[$user],
            'archived_at >=' => (new Moment())->subtractMinutes(5)->format('Y-m-d H:i:s'),
        ]);

        $I->sendGet('/v1/groups');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonHasValue('$.groups[*].id', HashID::encode($groupId));
    }

    /**
     * Archive user provider
     *
     * @return array[]
     */
    protected function archiveGroupProvider(): array
    {
        return [
            UserSeed::SECURITY_ADMIN . ' archives group ' . Group::SECURITY_ADMIN . '(' . ACLSeed::GROUP_ID[Group::SECURITY_ADMIN] . ')' => [
                'id' => ACLSeed::GROUP_ID[Group::SECURITY_ADMIN],
                'user' => UserSeed::SECURITY_ADMIN,
            ],
            UserSeed::SECURITY_ADMIN . ' archives group ' . Group::ADMIN . '(' . ACLSeed::GROUP_ID[Group::ADMIN] . ')' => [
                'id' => ACLSeed::GROUP_ID[Group::ADMIN],
                'user' => UserSeed::SECURITY_ADMIN,
            ],
            UserSeed::SECURITY_ADMIN . ' archives group ' . Group::USER . '(' . ACLSeed::GROUP_ID[Group::USER] . ')' => [
                'id' => ACLSeed::GROUP_ID[Group::USER],
                'user' => UserSeed::SECURITY_ADMIN,
            ],
        ];
    }

    /**
     * Archive user unsuccessfully provider
     *
     * @return array
     */
    protected function archiveGroupUnsuccessfullyProvider(): array
    {
        return [
            UserSeed::ADMIN . ' archives group ' . Group::SECURITY_ADMIN . '(' . ACLSeed::GROUP_ID[Group::SECURITY_ADMIN] . ')' => [
                'id' => ACLSeed::GROUP_ID[Group::SECURITY_ADMIN],
                'user' => UserSeed::ADMIN,
            ],
            UserSeed::ADMIN . ' archives group ' . Group::ADMIN . '(' . ACLSeed::GROUP_ID[Group::ADMIN] . ')' => [
                'id' => ACLSeed::GROUP_ID[Group::ADMIN],
                'user' => UserSeed::ADMIN,
            ],
            UserSeed::ADMIN . ' archives group ' . Group::USER . '(' . ACLSeed::GROUP_ID[Group::USER] . ')' => [
                'id' => ACLSeed::GROUP_ID[Group::USER],
                'user' => UserSeed::ADMIN,
            ],
            UserSeed::USER . ' archives group ' . Group::SECURITY_ADMIN . '(' . ACLSeed::GROUP_ID[Group::SECURITY_ADMIN] . ')' => [
                'id' => ACLSeed::GROUP_ID[Group::SECURITY_ADMIN],
                'user' => UserSeed::USER,
            ],
            UserSeed::USER . ' archives group ' . Group::ADMIN . '(' . ACLSeed::GROUP_ID[Group::ADMIN] . ')' => [
                'id' => ACLSeed::GROUP_ID[Group::ADMIN],
                'user' => UserSeed::USER,
            ],
            UserSeed::USER . ' archives group ' . Group::USER . '(' . ACLSeed::GROUP_ID[Group::USER] . ')' => [
                'id' => ACLSeed::GROUP_ID[Group::USER],
                'user' => UserSeed::ADMIN,
            ],
        ];
    }
}
