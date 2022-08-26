<?php


namespace Test\Api\User;

require_once __DIR__ . '/../../../resources/seeds/UserSeed.php';

use ApiTester;
use App\Service\ID\HashID;
use App\Table\UserTable;
use App\Type\HttpCode;
use Codeception\Example;
use Moment\Moment;
use UserSeed;

class UserArchiveCest
{
    /**
     * Try to archive a user successfully
     *
     * @dataProvider archiveUserProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToArchiveUser(ApiTester $I, Example $example)
    {
        $userId = $example->offsetGet('id');
        $user = $example->offsetGet('user');
        $I->amJWTAuthenticated($user);
        $I->sendPatch('/v1/users/' . HashID::encode($userId));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonHasValue('$.message', 'Archived user successfully');
        $I->seeResponseJsonMatchesJsonPath('$.user_id');
        $I->seeResponseJsonHasValue('$.user_id', HashID::encode($userId));

        $userHash = $I->grabDataFromResponseByJsonPath('$.user_id');

        $I->assertIsNotNumeric($userHash); // expect to be hashed

        $I->seeInDatabase(UserTable::getName(), [
            'id' => $userId,
            'archived_by' => UserSeed::USER_ID[$user],
            'archived_at >=' => (new Moment())->subtractMinutes(5)->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Try to archive a user unsuccessfully
     *
     * @dataProvider archiveUserUnsuccessfullyProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToArchiveUserUnsuccessfully(ApiTester $I, Example $example)
    {
        $userId = $example->offsetGet('id');
        $user = $example->offsetGet('user');
        $I->amJWTAuthenticated($user);
        $I->sendPatch('/v1/users/' . HashID::encode($userId));
        $I->seeNotAuthorized();
    }

    /**
     * Try to archive a user twice
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function tryToArchiveTwice(ApiTester $I)
    {
        $user = UserSeed::SECURITY_ADMIN;
        $userId = UserSeed::USER_ID[UserSeed::USER];
        $I->amJWTAuthenticated($user);
        $I->sendPatch('/v1/users/' . HashID::encode($userId));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonHasValue('$.message', 'Archived user successfully');

        $I->seeInDatabase(UserTable::getName(), [
            'id' => $userId,
            'archived_by' => UserSeed::USER_ID[$user],
            'archived_at >=' => (new Moment())->subtractMinutes(5)->format('Y-m-d H:i:s'),
        ]);

        $I->amJWTAuthenticated($user);
        $I->sendPatch('/v1/users/' . HashID::encode($userId));
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
    public function tryToArchiveAndThenNotSeeItAnymore(ApiTester $I)
    {
        $user = UserSeed::SECURITY_ADMIN;
        $userId = UserSeed::USER_ID[UserSeed::USER];
        $I->amJWTAuthenticated($user);
        $I->sendPatch('/v1/users/' . HashID::encode($userId));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonHasValue('$.message', 'Archived user successfully');

        $I->seeInDatabase(UserTable::getName(), [
            'id' => $userId,
            'archived_by' => UserSeed::USER_ID[$user],
            'archived_at >=' => (new Moment())->subtractMinutes(5)->format('Y-m-d H:i:s'),
        ]);

        $I->sendGet('/v1/users');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonHasValue('$.users[*].id', HashID::encode($userId));
    }

    /**
     * Archive user provider
     *
     * @return array[]
     */
    protected function archiveUserProvider(): array
    {
        return [
            UserSeed::SECURITY_ADMIN . ' archives user ' . UserSeed::SECURITY_ADMIN . '(' . UserSeed::USER_ID[UserSeed::SECURITY_ADMIN] . ')' => [
                'id' => UserSeed::USER_ID[UserSeed::SECURITY_ADMIN],
                'user' => UserSeed::SECURITY_ADMIN,
            ],
            UserSeed::SECURITY_ADMIN . ' archives user ' . UserSeed::ADMIN . '(' . UserSeed::USER_ID[UserSeed::ADMIN] . ')' => [
                'id' => UserSeed::USER_ID[UserSeed::ADMIN],
                'user' => UserSeed::SECURITY_ADMIN,
            ],
            UserSeed::SECURITY_ADMIN . ' archives user ' . UserSeed::USER . '(' . UserSeed::USER_ID[UserSeed::USER] . ')' => [
                'id' => UserSeed::USER_ID[UserSeed::USER],
                'user' => UserSeed::SECURITY_ADMIN,
            ],
            UserSeed::ADMIN . ' archives user ' . UserSeed::SECURITY_ADMIN . '(' . UserSeed::USER_ID[UserSeed::SECURITY_ADMIN] . ')' => [
                'id' => UserSeed::USER_ID[UserSeed::SECURITY_ADMIN],
                'user' => UserSeed::ADMIN,
            ],
            UserSeed::ADMIN . ' archives user ' . UserSeed::ADMIN . '(' . UserSeed::USER_ID[UserSeed::ADMIN] . ')' => [
                'id' => UserSeed::USER_ID[UserSeed::ADMIN],
                'user' => UserSeed::ADMIN,
            ],
            UserSeed::ADMIN . ' archives user ' . UserSeed::USER . '(' . UserSeed::USER_ID[UserSeed::USER] . ')' => [
                'id' => UserSeed::USER_ID[UserSeed::USER],
                'user' => UserSeed::ADMIN,
            ],
            UserSeed::USER . ' archives user ' . UserSeed::USER . '(' . UserSeed::USER_ID[UserSeed::USER] . ')' => [
                'id' => UserSeed::USER_ID[UserSeed::USER],
                'user' => UserSeed::USER,
            ],
        ];
    }

    /**
     * Archive user unsuccessfully provider
     *
     * @return array
     */
    protected function archiveUserUnsuccessfullyProvider(): array
    {
        return [
            UserSeed::USER . ' archives user ' . UserSeed::ADMIN . '(' . UserSeed::USER_ID[UserSeed::ADMIN] . ')' => [
                'id' => UserSeed::USER_ID[UserSeed::ADMIN],
                'user' => UserSeed::USER,
            ],
            UserSeed::USER . ' archives user ' . UserSeed::SECURITY_ADMIN . '(' . UserSeed::USER_ID[UserSeed::SECURITY_ADMIN] . ')' => [
                'id' => UserSeed::USER_ID[UserSeed::SECURITY_ADMIN],
                'user' => UserSeed::USER,
            ],
        ];
    }
}
