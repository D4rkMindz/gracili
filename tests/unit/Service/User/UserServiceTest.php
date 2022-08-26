<?php


namespace Unit\Service\User;

use App\Exception\RecordNotFoundException;
use App\Service\User\UserService;
use UnitTester;
use UserSeed;

class UserServiceTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;
    protected UserService $userService;

    /**
     * Test the getIdByUsername method
     *
     * @dataProvider getIdByUsername
     *
     * @param string $username
     * @param int    $expected
     *
     * @return void
     */
    public function testGetIdByUsername(string $username, int $expected): void
    {
        $actual = $this->userService->getIdByUsername($username);
        $this->assertSame($expected, $actual);
    }

    /**
     * Data provider for testGetIdByUsername
     *
     * @return array
     */
    public function getIdByUsername(): array
    {
        require_once __DIR__ . '/../../../../resources/seeds/UserSeed.php';


        return [
            'test by email' => ['security_admin@your-domain.com', UserSeed::USER_ID[UserSeed::SECURITY_ADMIN]],
            'test by username' => ['security_admin', UserSeed::USER_ID[UserSeed::SECURITY_ADMIN]],
        ];
    }

    /**
     * Test getIdByUsername method with invalid data
     *
     * @return void
     */
    public function testGetIdByUsernameWithInvalidUsername(): void
    {
        $this->expectException(RecordNotFoundException::class);
        $this->userService->getIdByUsername('this username does not exist');
    }

    protected function _before()
    {
        $this->userService = $this->tester->getContainer()->get(UserService::class);
    }
}
