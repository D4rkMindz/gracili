<?php

namespace Unit\Service\Auth;

use App\Service\Auth\AuthService;
use UnitTester;
use UserSeed;

class AuthServiceTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;
    protected AuthService $auth;

    /**
     * Test if canLogin works as expected
     *
     * @dataProvider canLogin
     *
     * @param int $userId
     * @param string $password
     * @param bool $expected
     *
     * @return void
     */
    public function testCanLogin(int $userId, string $password, bool $expected): void
    {
        $actual = $this->auth->canLogin($userId, $password);
        $this->assertSame($expected, $actual);
    }

    /**
     * Data provider for testCanLogin
     *
     * @return array
     */
    public function canLogin(): array
    {
        require_once __DIR__ . '/../../../../resources/seeds/UserSeed.php';

        return [
            'Test security_admin with correct password' => [
                UserSeed::USER_ID[UserSeed::SECURITY_ADMIN],
                UserSeed::SECURITY_ADMIN,
                true,
            ],
            'Test admin with correct password' => [UserSeed::USER_ID[UserSeed::ADMIN], UserSeed::ADMIN, true],
            'Test user with correct password' => [UserSeed::USER_ID[UserSeed::USER], UserSeed::USER, true],
            'Test admin with incorrect password' => [UserSeed::USER_ID[UserSeed::ADMIN], 'notavalidpassword', false],
        ];
    }

    protected function _before()
    {
        $this->auth = $this->tester->getContainer()->get(AuthService::class);
    }
}
