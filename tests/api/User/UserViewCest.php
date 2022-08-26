<?php


namespace Test\Api\User;

require_once __DIR__ . '/../../../resources/seeds/UserSeed.php';

use ApiTester;
use App\Service\ID\HashID;
use App\Type\HttpCode;
use Codeception\Example;
use UserSeed;

class UserViewCest
{
    /**
     * Testing view all
     *
     * @dataProvider viewUserProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToViewUser(ApiTester $I, Example $example)
    {
        $authenticatedUser = $example->offsetGet('authenticated_user');
        $userId = $example->offsetGet('user_id');
        $I->amJWTAuthenticated($authenticatedUser);
        $I->sendGet('/v1/users/' . HashID::encode($userId));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonMatchesJsonPath('$.user');
        $user = $I->grabDataFromResponseByJsonPath('$.user')[0];
        $I->assertArrayNotHasKey('password', $user);

        $I->assertArrayHasKey('id', $user);
        $I->assertIsNotNumeric($user['id']); // expect to be hashed
        $I->assertSame(HashID::encode($userId), $user['id']);

        $I->assertArrayHasKey('username', $user);
        $I->assertArrayHasKey('first_name', $user);
        $I->assertArrayHasKey('last_name', $user);
        $I->assertArrayHasKey('email', $user);
        $I->assertArrayHasKey('last_login_at', $user);
        $I->assertArrayHasKey('language', $user);
        $I->assertArrayHasKey('id', $user['language']);
        $I->assertIsNotNumeric($user['language']['id']); // expect to be hashed
        $I->assertArrayHasKey('tag', $user['language']);
        $I->assertArrayHasKey('name', $user['language']);
        $I->assertArrayHasKey('english_name', $user['language']);
    }

    /**
     * Data provider for viewing user data that should not be allowed
     *
     * @return array[]
     */
    public function viewUserUnauthorizedProvider(): array
    {
        return [
            // Viewing another user as security_admin MUST be possible
            UserSeed::USER . ' viewing ' . UserSeed::ADMIN => [
                'authenticated_user' => UserSeed::USER,
                'user_id' => UserSeed::USER_ID[UserSeed::ADMIN],
            ],
            UserSeed::ADMIN . ' viewing ' . UserSeed::SECURITY_ADMIN => [
                'authenticated_user' => UserSeed::SECURITY_ADMIN,
                'user_id' => UserSeed::USER_ID[UserSeed::ADMIN],
            ],
            // viewing yourself MUST be possible
            UserSeed::USER . ' viewing ' . UserSeed::USER => [
                'authenticated_user' => UserSeed::USER,
                'user_id' => UserSeed::USER_ID[UserSeed::USER],
            ],
        ];
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
        $I->sendGet('/v1/users/' . $example->offsetGet('user_id'));
        $I->seeNotAuthorized();
    }

    /**
     * Data provider for viewing users successfully
     *
     * @return array[]
     */
    protected function viewUserProvider(): array
    {
        return [
            // Viewing another user as security_admin MUST be possible
            UserSeed::SECURITY_ADMIN . ' viewing ' . UserSeed::USER => [
                'authenticated_user' => UserSeed::SECURITY_ADMIN,
                'user_id' => UserSeed::USER_ID[UserSeed::USER],
            ],
            UserSeed::SECURITY_ADMIN . ' viewing ' . UserSeed::ADMIN => [
                'authenticated_user' => UserSeed::SECURITY_ADMIN,
                'user_id' => UserSeed::USER_ID[UserSeed::ADMIN],
            ],
            // Admin has all roles except security_admin
            UserSeed::ADMIN . ' viewing ' . UserSeed::SECURITY_ADMIN => [
                'authenticated_user' => UserSeed::ADMIN,
                'user_id' => UserSeed::USER_ID[UserSeed::ADMIN],
            ],
            // viewing yourself MUST be possible
            UserSeed::USER . ' viewing ' . UserSeed::USER => [
                'authenticated_user' => UserSeed::USER,
                'user_id' => UserSeed::USER_ID[UserSeed::USER],
            ],
        ];
    }
}
