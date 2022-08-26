<?php


namespace Test\Api\User;

require_once __DIR__ . '/../../../resources/seeds/UserSeed.php';

use ApiTester;
use App\Service\ID\HashID;
use App\Table\UserTable;
use App\Type\HttpCode;
use Codeception\Example;
use UserSeed;

class UserDeleteCest
{
    /**
     * Try to delete user successfully
     *
     * @dataProvider userDeleteProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToDeleteUser(ApiTester $I, Example $example)
    {
        $userID = $example->offsetGet('id');
        $I->amJWTAuthenticated(UserSeed::SECURITY_ADMIN);
        $I->sendDelete('/v1/users/' . HashID::encode($userID));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonHasValue('$.message', 'Deleted user successfully');
        $I->seeResponseJsonMatchesJsonPath('$.user_id');
        $I->seeResponseJsonHasValue('$.user_id', HashID::encode($userID));

        $userHash = $I->grabDataFromResponseByJsonPath('$.user_id');

        $I->assertIsNotNumeric($userHash); // expect to be hashed

        $I->dontSeeInDatabase(UserTable::getName(), ['id' => $userID]);
    }

    /**
     * Try to delete a user using a user that is not authorized
     *
     * @dataProvider userDeleteUnauthorizedProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToDeleteUserUnauthorized(ApiTester $I, Example $example)
    {
        $user = $example->offsetGet('user');
        $userID = $example->offsetGet('id');
        $I->amJWTAuthenticated($user);
        $I->sendDelete('/v1/users/' . HashID::encode($userID));
        $I->seeNotAuthorized();
        $I->seeInDatabaseExcludingArchived(UserTable::getName(), ['id' => $userID]);
    }

    /**
     * Try to archive a user twice
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function tryToDeleteTwice(ApiTester $I)
    {
        $user = UserSeed::SECURITY_ADMIN;
        $userId = UserSeed::USER_ID[UserSeed::USER];
        $I->amJWTAuthenticated($user);
        $I->sendDelete('/v1/users/' . HashID::encode($userId));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonHasValue('$.message', 'Deleted user successfully');

        $I->dontSeeInDatabase(UserTable::getName(), ['id' => $userId]);

        $I->amJWTAuthenticated($user);
        $I->sendDelete('/v1/users/' . HashID::encode($userId));
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
    protected function userDeleteUnauthorizedProvider(): array
    {
        return [
            UserSeed::USER . ' trying to delete ' . UserSeed::USER . ' (' . UserSeed::USER_ID[UserSeed::USER] . ')' => [
                'user' => UserSeed::USER,
                'id' => UserSeed::USER_ID[UserSeed::USER],
            ],
            UserSeed::USER . ' trying to delete ' . UserSeed::ADMIN . ' (' . UserSeed::USER_ID[UserSeed::ADMIN] . ')' => [
                'user' => UserSeed::USER,
                'id' => UserSeed::USER_ID[UserSeed::ADMIN],
            ],
            UserSeed::USER . ' trying to delete ' . UserSeed::SECURITY_ADMIN . ' (' . UserSeed::USER_ID[UserSeed::SECURITY_ADMIN] . ')' => [
                'user' => UserSeed::USER,
                'id' => UserSeed::USER_ID[UserSeed::SECURITY_ADMIN],
            ],
            UserSeed::ADMIN . ' trying to delete ' . UserSeed::USER . ' (' . UserSeed::USER_ID[UserSeed::USER] . ')' => [
                'user' => UserSeed::ADMIN,
                'id' => UserSeed::USER_ID[UserSeed::USER],
            ],
            UserSeed::ADMIN . ' trying to delete ' . UserSeed::ADMIN . ' (' . UserSeed::USER_ID[UserSeed::ADMIN] . ')' => [
                'user' => UserSeed::ADMIN,
                'id' => UserSeed::USER_ID[UserSeed::ADMIN],
            ],
            UserSeed::ADMIN . ' trying to delete ' . UserSeed::SECURITY_ADMIN . ' (' . UserSeed::USER_ID[UserSeed::SECURITY_ADMIN] . ')' => [
                'user' => UserSeed::ADMIN,
                'id' => UserSeed::USER_ID[UserSeed::SECURITY_ADMIN],
            ],
        ];
    }

    /**
     * Delete user data provider
     *
     * @return array[]
     */
    protected function userDeleteProvider(): array
    {
        return [
            'Delete user ' . UserSeed::SECURITY_ADMIN . '(' . UserSeed::USER_ID[UserSeed::SECURITY_ADMIN] . ')' => ['id' => UserSeed::USER_ID[UserSeed::SECURITY_ADMIN]],
            'Delete user ' . UserSeed::ADMIN . '(' . UserSeed::USER_ID[UserSeed::ADMIN] . ')' => ['id' => UserSeed::USER_ID[UserSeed::ADMIN]],
            'Delete user ' . UserSeed::USER . '(' . UserSeed::USER_ID[UserSeed::USER] . ')' => ['id' => UserSeed::USER_ID[UserSeed::USER]],
        ];
    }
}
