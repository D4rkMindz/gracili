<?php


namespace Test\Api\User\Group;

require_once __DIR__ . '/../../../../resources/seeds/UserSeed.php';
require_once __DIR__ . '/../../../../resources/seeds/ACLSeed.php';

use ACLSeed;
use ApiTester;
use App\Service\ID\HashID;
use App\Type\Auth\Group;
use App\Type\HttpCode;
use Codeception\Example;
use UserSeed;

class UserGroupsViewAllCest
{
    /**
     * Try to view users groups
     *
     * @dataProvider viewAllGroupsProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToViewAllGroupsOfAUser(ApiTester $I, Example $example)
    {
        $userId = $example->offsetGet('id');
        $user = $example->offsetGet('user');
        $groups = $example->offsetGet('groups');
        $I->amJWTAuthenticated($user);
        $I->sendGet('/v1/users/' . HashID::encode($userId) . '/groups');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonMatchesJsonPath('$.groups');
        $response = $I->grabDataFromResponseByJsonPath('$')[0];
        $count = count($response['groups']);
        $I->seeResponseJsonHasValue('$.message', 'Found ' . $count . ' groups');
        $I->seeResponseJsonHasValue('$.count', $count);
        foreach ($groups as $group) {
            $I->seeResponseJsonHasValue('$.groups[*].id', $group['id']);
        }
    }

    /**
     * Try to view users groups
     *
     * @dataProvider viewAllGroupsUnauthorizedProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToViewAllGroupsOfAUserUnauthorized(ApiTester $I, Example $example)
    {
        $userId = $example->offsetGet('id');
        $user = $example->offsetGet('user');
        $I->amJWTAuthenticated($user);
        $I->sendGet('/v1/users/' . HashID::encode($userId) . '/groups');
        $I->seeNotAuthorized();
    }

    /**
     * View all groups data provider
     *
     * @return array|array[]
     */
    public function viewAllGroupsProvider(): array
    {
        return [
            UserSeed::SECURITY_ADMIN . ' viewing ' . UserSeed::USER => [
                'id' => UserSeed::USER_ID[UserSeed::USER],
                'user' => UserSeed::SECURITY_ADMIN,
                'groups' => [
                    ['id' => HashID::encode(ACLSeed::GROUP_ID[Group::USER])],
                ],
            ],
            UserSeed::SECURITY_ADMIN . ' viewing ' . UserSeed::SECURITY_ADMIN => [
                'id' => UserSeed::USER_ID[UserSeed::SECURITY_ADMIN],
                'user' => UserSeed::SECURITY_ADMIN,
                'groups' => [
                    ['id' => HashID::encode(ACLSeed::GROUP_ID[Group::SECURITY_ADMIN])],
                ],
            ],
            // a user should see their own groups
            UserSeed::USER . ' viewing ' . UserSeed::USER => [
                'id' => UserSeed::USER_ID[UserSeed::USER],
                'user' => UserSeed::USER,
                'groups' => [
                    ['id' => HashID::encode(ACLSeed::GROUP_ID[Group::USER])],
                ],
            ],
        ];
    }

    /**
     * View all groups unauthorized data provider
     *
     * @return array
     */
    public function viewAllGroupsUnauthorizedProvider(): array
    {
        return [
            UserSeed::USER . ' trying to view ' . UserSeed::ADMIN . '\'s groups' => [
                'id' => UserSeed::USER_ID[UserSeed::ADMIN],
                'user' => UserSeed::USER,
            ],
            UserSeed::ADMIN . ' trying to view ' . UserSeed::USER . '\'s groups' => [
                'id' => UserSeed::USER_ID[UserSeed::USER],
                'user' => UserSeed::ADMIN,
            ],
        ];
    }
}
