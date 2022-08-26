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

class UserGroupsDeleteCest
{
    /**
     * Try to view users groups
     *
     * @dataProvider removeGroupProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToRemoveAGroupFromAUser(ApiTester $I, Example $example)
    {
        $userId = $example->offsetGet('id');
        $groupId = $example->offsetGet('group');
        $I->amJWTAuthenticated(UserSeed::SECURITY_ADMIN);
        $I->sendDelete('/v1/users/' . HashID::encode($userId) . '/groups/' . HashID::encode($groupId));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonHasValue('$.message', 'Successfully removed group from user');
        $I->seeResponseJsonMatchesJsonPath('$.group');
        $I->seeResponseJsonHasValue('$.group.id', HashID::encode($groupId));

        $I->dontSeeInDatabaseExcludingArchived(UserHasGroupTable::getName(),
            ['user_id' => $userId, 'group_id' => $groupId]);
    }

    /**
     * Try to view users groups
     *
     * @dataProvider removeGroupUnauthorizedProvider
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
        $I->sendDelete('/v1/users/' . HashID::encode($userId) . '/groups/' . HashID::encode($groupId));
        $I->seeNotAuthorized();
    }

    /**
     * Try to add a group to a user that already has that group assigned
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function tryToRemoveAGroupFromAUserThatAlreadyDontHasGroup(ApiTester $I)
    {
        $I->amJWTAuthenticated(UserSeed::SECURITY_ADMIN);

        $userId = UserSeed::USER_ID[UserSeed::ADMIN];
        $groupId = ACLSeed::GROUP_ID[Group::SECURITY_ADMIN];

        $I->dontSeeInDatabaseExcludingArchived(UserHasGroupTable::getName(),
            ['user_id' => $userId, 'group_id' => $groupId]);
        $I->sendDelete('/v1/users/' . HashID::encode($userId) . '/groups/' . HashID::encode($groupId));
        $I->seeValidationErrors('Please check your data', [
            [
                'field' => 'group',
                'message' => 'Group not assigned to user',
            ],
        ]);
        $I->dontSeeInDatabaseExcludingArchived(UserHasGroupTable::getName(),
            ['user_id' => $userId, 'group_id' => $groupId]);
    }

    /**
     * Add a group to a user data provider
     *
     * @return array|array[]
     */
    protected function removeGroupProvider(): array
    {
        return [
            UserSeed::SECURITY_ADMIN . ' removing ' . Group::ADMIN . ' from ' . UserSeed::ADMIN => [
                'id' => UserSeed::USER_ID[UserSeed::ADMIN],
                'group' => ACLSeed::GROUP_ID[Group::ADMIN],
            ],
            UserSeed::SECURITY_ADMIN . ' removing ' . Group::USER . ' from ' . UserSeed::USER => [
                'id' => UserSeed::USER_ID[UserSeed::USER],
                'group' => ACLSeed::GROUP_ID[Group::USER],
            ],
        ];
    }

    /**
     * Add a group to a user as unauthorized user data provider
     *
     * @return array
     */
    protected function removeGroupUnauthorizedProvider(): array
    {
        return [
            UserSeed::USER . ' trying to remove ' . Group::ADMIN . ' from ' . UserSeed::ADMIN . '\'s groups' => [
                'id' => UserSeed::USER_ID[UserSeed::ADMIN],
                'user' => UserSeed::USER,
                'group' => ACLSeed::GROUP_ID[Group::ADMIN],
            ],
            UserSeed::ADMIN . ' trying to remove ' . Group::SECURITY_ADMIN . ' from ' . UserSeed::SECURITY_ADMIN . '\'s groups' => [
                'id' => UserSeed::USER_ID[UserSeed::SECURITY_ADMIN],
                'user' => UserSeed::ADMIN,
                'group' => ACLSeed::GROUP_ID[Group::SECURITY_ADMIN],
            ],
        ];
    }
}
