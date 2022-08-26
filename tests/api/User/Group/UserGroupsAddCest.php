<?php


namespace Test\Api\User\Group;

require_once __DIR__ . '/../../../../resources/seeds/UserSeed.php';
require_once __DIR__ . '/../../../../resources/seeds/ACLSeed.php';

use ACLSeed;
use ApiTester;
use App\Service\ID\HashID;
use App\Table\UserHasGroupTable;
use App\Type\Auth\Group;
use App\Type\HttpCode;
use Codeception\Example;
use UserSeed;

class UserGroupsAddCest
{
    /**
     * Try to view users groups
     *
     * @dataProvider addGroupProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToAddGroupToAUser(ApiTester $I, Example $example)
    {
        $userId = $example->offsetGet('id');
        $user = $example->offsetGet('user');
        $groupId = $example->offsetGet('group');
        $I->amJWTAuthenticated($user);
        $I->sendPost('/v1/users/' . HashID::encode($userId) . '/groups/' . HashID::encode($groupId));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonHasValue('$.message', 'Successfully assigned group to user');
        $I->seeResponseJsonMatchesJsonPath('$.group');
        $I->seeResponseJsonHasValue('$.group.id', HashID::encode($groupId));

        $I->seeInDatabaseExcludingArchived(UserHasGroupTable::getName(),
            ['user_id' => $userId, 'group_id' => $groupId]);
    }

    /**
     * Try to view users groups
     *
     * @dataProvider addGroupUnauthorizedProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToAddGroupToAUserUnauthorized(ApiTester $I, Example $example)
    {
        $userId = $example->offsetGet('id');
        $user = $example->offsetGet('user');
        $groupId = $example->offsetGet('group');
        $I->amJWTAuthenticated($user);
        $I->sendPost('/v1/users/' . HashID::encode($userId) . '/groups/' . HashID::encode($groupId));
        $I->seeNotAuthorized();
    }

    /**
     * Try to add a group to a user that already has that group assigned
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function tryToAddGroupToAUserThatAlreadyHasGroup(ApiTester $I)
    {
        $I->amJWTAuthenticated(UserSeed::SECURITY_ADMIN);

        $userId = UserSeed::USER_ID[UserSeed::ADMIN];
        $groupId = ACLSeed::GROUP_ID[Group::ADMIN];

        $I->seeInDatabaseExcludingArchived(UserHasGroupTable::getName(),
            ['user_id' => $userId, 'group_id' => $groupId]);
        $I->sendPost('/v1/users/' . HashID::encode($userId) . '/groups/' . HashID::encode($groupId));
        $I->seeValidationErrors('Please check your data', [
            [
                'field' => 'group',
                'message' => 'Group already assigned',
            ],
        ]);
        $I->seeInDatabaseExcludingArchived(UserHasGroupTable::getName(),
            ['user_id' => $userId, 'group_id' => $groupId]);
    }

    /**
     * Add a group to a user data provider
     *
     * @return array|array[]
     */
    protected function addGroupProvider(): array
    {
        return [
            UserSeed::SECURITY_ADMIN . ' adding ' . Group::ADMIN . ' to ' . UserSeed::USER => [
                'id' => UserSeed::USER_ID[UserSeed::USER],
                'user' => UserSeed::SECURITY_ADMIN,
                'group' => ACLSeed::GROUP_ID[Group::ADMIN],
            ],
            UserSeed::SECURITY_ADMIN . ' adding ' . Group::USER . ' to ' . UserSeed::ADMIN => [
                'id' => UserSeed::USER_ID[UserSeed::SECURITY_ADMIN],
                'user' => UserSeed::SECURITY_ADMIN,
                'group' => ACLSeed::GROUP_ID[Group::USER],
            ],
        ];
    }

    /**
     * Add a group to a user as unauthorized user data provider
     *
     * @return array
     */
    protected function addGroupUnauthorizedProvider(): array
    {
        return [
            UserSeed::USER . ' trying to add ' . Group::ADMIN . ' to ' . UserSeed::USER . '\'s groups' => [
                'user' => UserSeed::USER,
                'group' => ACLSeed::GROUP_ID[Group::ADMIN],
                'id' => UserSeed::USER_ID[UserSeed::USER],
            ],
            UserSeed::ADMIN . ' trying to add ' . Group::SECURITY_ADMIN . ' to ' . UserSeed::USER . '\'s groups' => [
                'user' => UserSeed::ADMIN,
                'group' => ACLSeed::GROUP_ID[Group::SECURITY_ADMIN],
                'id' => UserSeed::USER_ID[UserSeed::USER],
            ],
        ];
    }
}
