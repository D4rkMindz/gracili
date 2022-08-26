<?php

namespace Test\Api\Auth;

use ApiTester;
use App\Type\HttpCode;

class UserLoginCest
{
    /**
     * Try to login without any data
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function tryToLoginWithoutData(ApiTester $I)
    {
        $I->sendPost('/v1/auth/login');
        $this->expectLoginFailed($I);
    }

    /**
     * Verify that the login request failed and that it contains the expected data
     *
     * @param ApiTester $I
     *
     * @return void
     */
    private function expectLoginFailed(ApiTester $I)
    {
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', false);
        $I->seeResponseJsonHasValue('$.message', 'Username or password invalid');
        $I->seeResponseJsonHasValue('$.error.message', 'Username or password invalid');
        $I->seeResponseJsonHasValue('$.error.fields[0].field', 'username');
        $I->seeResponseJsonHasValue('$.error.fields[0].message', 'Username or password invalid');
        $I->dontSeeResponseJsonMatchesJsonPath('$.error.fields[1]');
    }

    /**
     * Try to login with the correct user data
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function tryToLoginWithCorrectData(ApiTester $I)
    {
        $I->sendPost('/v1/auth/login', [
            'username' => 'admin',
            'password' => 'admin',
        ]);
        $this->expectLoginSuccessful($I);
    }

    /**
     * Verify that the login request was successful and that it contains the expected data
     *
     * @param ApiTester $I
     *
     * @return void
     */
    private function expectLoginSuccessful(ApiTester $I)
    {
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonHasValue('$.message', 'Login successful');
        $I->seeResponseJsonMatchesJsonPath('$.jwt');
    }

    /**
     * Try to login with invalid user data
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function tryToLoginWithInvalidData(ApiTester $I)
    {
        $I->sendPost('/v1/auth/login', [
            'username' => 'admin',
            'password' => 'wrong password',
        ]);
        $this->expectLoginFailed($I);
    }
}
