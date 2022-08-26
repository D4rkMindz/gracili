<?php

namespace Test\Api\User;

require_once __DIR__ . '/../../../resources/seeds/UserSeed.php';
require_once __DIR__ . '/../../../resources/seeds/LanguageSeed.php';

use ApiTester;
use App\Service\ID\HashID;
use App\Table\UserTable;
use App\Type\HttpCode;
use App\Type\Language;
use Codeception\Example;
use LanguageSeed;
use UserSeed;

class UserEditCest
{
    /**
     * Test updating a user
     *
     * @dataProvider userEditSuccessfulProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToUpdateUserSuccessfullyTest(ApiTester $I, Example $example)
    {
        $I->amJWTAuthenticated($example->offsetGet('user'));
        $id = $example->offsetGet('id');
        $I->sendPut('/v1/users/' . HashID::encode($id), $example->offsetGet('body'));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonHasValue('$.message', 'Updated user successfully');
        $I->seeInDatabaseExcludingArchived(UserTable::getName(),
            array_merge_recursive(['id' => $id], $example->offsetGet('verify')));
    }

    /**
     * Test updating a user
     *
     * @dataProvider userEditUnsuccessfulProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToUpdateUserUnsuccessfullyTest(ApiTester $I, Example $example)
    {
        $I->amJWTAuthenticated($example->offsetGet('user'));
        $id = $example->offsetGet('id');
        $I->sendPut('/v1/users/' . HashID::encode($id), $example->offsetGet('body'));
        $I->seeNotAuthorized();
    }


    /**
     * User edit (successfully) provider
     *
     * @return array[]
     */
    protected function userEditSuccessfulProvider(): array
    {
        return [
            'security_admin updating user.all' => [
                'id' => UserSeed::USER_ID[UserSeed::USER],
                'user' => UserSeed::SECURITY_ADMIN,
                'body' => [
                    'username' => 'new_user',
                    'language' => Language::DE_CH,
                    'email' => 'new@your-domain.org',
                    'first_name' => 'user updated',
                    'last_name' => 'user updated',
                ],
                'verify' => [
                    'username' => 'new_user',
                    'language_id' => LanguageSeed::LANGUAGE_ID[Language::DE_CH],
                    'email' => 'new@your-domain.org',
                    'email_verified' => false,
                    'first_name' => 'user updated',
                    'last_name' => 'user updated',
                ],
            ],
            'security_admin updating user.language' => [
                'id' => UserSeed::USER_ID[UserSeed::USER],
                'user' => UserSeed::SECURITY_ADMIN,
                'body' => [
                    'language' => Language::DE_CH,
                ],
                'verify' => [
                    'language_id' => LanguageSeed::LANGUAGE_ID[Language::DE_CH],
                ],
            ],
            'security_admin updating user.username' => [
                'id' => UserSeed::USER_ID[UserSeed::USER],
                'user' => UserSeed::SECURITY_ADMIN,
                'body' => [
                    'username' => 'new_user',
                ],
                'verify' => [
                    'username' => 'new_user',
                ],
            ],
            'security_admin updating user.email' => [
                'id' => UserSeed::USER_ID[UserSeed::USER],
                'user' => UserSeed::SECURITY_ADMIN,
                'body' => [
                    'email' => 'new@your-domain.org',
                ],
                'verify' => [
                    'email' => 'new@your-domain.org',
                    'email_verified' => false,
                ],
            ],
            'security_admin updating user.first_name' => [
                'id' => UserSeed::USER_ID[UserSeed::USER],
                'user' => UserSeed::SECURITY_ADMIN,
                'body' => [
                    'first_name' => 'user updated',
                ],
                'verify' => [
                    'first_name' => 'user updated',
                ],
            ],
            'security_admin updating user.last_name' => [
                'id' => UserSeed::USER_ID[UserSeed::USER],
                'user' => UserSeed::SECURITY_ADMIN,
                'body' => [
                    'last_name' => 'user updated',
                ],
                'verify' => [
                    'last_name' => 'user updated',
                ],
            ],
            'user updating user.email' => [
                'id' => UserSeed::USER_ID[UserSeed::USER],
                'user' => UserSeed::USER,
                'body' => [
                    'email' => 'new@your-domain.org',
                ],
                'verify' => [
                    'email' => 'new@your-domain.org',
                    'email_verified' => false,
                ],
            ],
        ];
    }

    /**
     * User edit unsuccessful data provider
     *
     * @return array[]
     */
    protected function userEditUnsuccessfulProvider(): array
    {
        return [
            'user updating security_admin.email' => [
                'id' => UserSeed::USER_ID[UserSeed::SECURITY_ADMIN],
                'user' => UserSeed::USER,
                'body' => [
                    'email' => 'new@your-domain.org',
                ],
            ],
        ];
    }
}
