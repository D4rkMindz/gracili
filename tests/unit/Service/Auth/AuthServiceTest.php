<?php

namespace Unit\Service\Auth;

use App\Service\Auth\AuthService;
use UnitTester;
use UserSeed;

class AuthServiceTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;
    protected AuthService $auth;

    protected function _before()
    {
        $this->auth = $this->tester->getContainer()->get(AuthService::class);
    }

    /**
     * Test if canLogin works as expected
     *
     * @dataProvider canLogin
     *
     * @param int    $userId
     * @param string $password
     * @param bool   $expected
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
            'Test security_admin with correct password' => [UserSeed::USER['security_admin'], 'security_admin', true],
            'Test admin with correct password' => [UserSeed::USER['admin'], 'admin', true],
            'Test user with correct password' => [UserSeed::USER['user'], 'user', true],
            'Test admin with incorrect password' => [UserSeed::USER['admin'], 'notavalidpassword', false],
        ];
    }
}
