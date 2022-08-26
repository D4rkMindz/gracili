<?php


namespace Test\Api\User;

require_once __DIR__ . '/../../../resources/seeds/UserSeed.php';
require_once __DIR__ . '/../../../resources/seeds/LanguageSeed.php';

use ApiTester;
use App\Service\ID\HashID;
use App\Table\UserTable;
use App\Type\HttpCode;
use App\Type\Language;
use App\Type\User\RegistrationMethod;
use Codeception\Example;
use LanguageSeed;
use UserSeed;

class UserCreateCest
{
    /**
     * Test creating a user
     *
     * @dataProvider userCreateProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToCreateAUser(ApiTester $I, Example $example)
    {
        $authenticatedUser = $example->offsetGet('authenticated_user');
        $userData = $example->offsetGet('user');
        $verifyData = $example->offsetGet('verify');
        $I->amJWTAuthenticated($authenticatedUser);
        $I->sendPost('/v1/users', $userData);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonHasValue('$.message', 'Created user successfully');
        $I->seeResponseJsonMatchesJsonPath('$.user');

        $user = $I->grabDataFromResponseByJsonPath('$.user')[0];
        $I->assertArrayNotHasKey('password', $user);

        $I->assertArrayHasKey('id', $user);
        $I->assertIsNotNumeric($user['id']); // expect to be hashed

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

        // verify the data
        $I->seeResponseJsonHasValue('$.user.username', $verifyData['username']);
        $I->seeResponseJsonHasValue('$.user.email', $verifyData['email']);
        $I->seeResponseJsonHasValue('$.user.first_name', $verifyData['first_name']);
        $I->seeResponseJsonHasValue('$.user.last_name', $verifyData['last_name']);
        $I->seeResponseJsonHasValue('$.user.language.id', HashID::encode($verifyData['language_id']));

        $I->seeInDatabaseExcludingArchived(UserTable::getName(), $verifyData);
    }

    /**
     * Try to test unauthorized
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function tryToCreateAUserUnauthorized(ApiTester $I)
    {
        $user = UserSeed::USER;
        $I->amJWTAuthenticated($user);
        $I->sendPost('/v1/users', [
            'email' => 'newuser@your-domain.com',
            'username' => 'new_user',
            'first_name' => 'app',
            'password' => 'Password!12',
        ]);
        $I->seeNotAuthorized();
    }

    /**
     * Data provider for creating users
     *
     * @return array[]
     */
    protected function userCreateProvider(): array
    {
        return [
            UserSeed::SECURITY_ADMIN . ' creating a user (minimum)' => [
                'authenticated_user' => UserSeed::SECURITY_ADMIN,
                'user' => [
                    'email' => 'newuser@your-domain.com',
                    'username' => 'new_user',
                    'first_name' => 'app',
                    'password' => 'Password!12',
                ],
                'verify' => [
                    'username' => 'new_user',
                    'first_name' => 'app',
                    'language_id' => LanguageSeed::LANGUAGE_ID[Language::EN_GB],
                    'last_name' => null,
                    'email' => 'newuser@your-domain.com',
                    'email_verified' => false,
                    'registration_method' => RegistrationMethod::DEFAULT,
                ],
            ],
            UserSeed::ADMIN . ' creating a user (minimum)' => [
                'authenticated_user' => UserSeed::ADMIN,
                'user' => [
                    'email' => 'newuser@your-domain.com',
                    'username' => 'new_user',
                    'first_name' => 'app',
                    'password' => 'Password!12',
                ],
                'verify' => [
                    'email' => 'newuser@your-domain.com',
                    'username' => 'new_user',
                    'first_name' => 'app',
                    'language_id' => LanguageSeed::LANGUAGE_ID[Language::EN_GB],
                    'last_name' => null,
                    'email_verified' => false,
                    'registration_method' => RegistrationMethod::DEFAULT,
                ],
            ],
            UserSeed::SECURITY_ADMIN . ' creating a user (maximum)' => [
                'authenticated_user' => UserSeed::SECURITY_ADMIN,
                'user' => [
                    'email' => 'newuser@your-domain.com',
                    'username' => 'new_user',
                    'first_name' => 'app',
                    'last_name' => 'user',
                    'password' => 'Password!12',
                    'language' => Language::DE_CH,
                ],
                'verify' => [
                    'email' => 'newuser@your-domain.com',
                    'username' => 'new_user',
                    'first_name' => 'app',
                    'language_id' => LanguageSeed::LANGUAGE_ID[Language::DE_CH],
                    'last_name' => 'user',
                    'email_verified' => false,
                    'registration_method' => RegistrationMethod::DEFAULT,
                ],
            ],
            UserSeed::ADMIN . ' creating a user (maximum)' => [
                'authenticated_user' => UserSeed::ADMIN,
                'user' => [
                    'email' => 'newuser@your-domain.com',
                    'username' => 'new_user',
                    'first_name' => 'app',
                    'last_name' => 'user',
                    'password' => 'Password!12',
                    'language' => Language::DE_CH,
                ],
                'verify' => [
                    'email' => 'newuser@your-domain.com',
                    'username' => 'new_user',
                    'first_name' => 'app',
                    'language_id' => LanguageSeed::LANGUAGE_ID[Language::DE_CH],
                    'last_name' => 'user',
                    'email_verified' => false,
                    'registration_method' => RegistrationMethod::DEFAULT,
                ],
            ],
        ];
    }
}
