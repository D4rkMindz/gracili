<?php


namespace Unit\Service\Auth;

use App\Service\Auth\AuthorizationService;
use App\Type\Auth\Group;
use App\Type\Auth\Role;
use UnitTester;
use UserSeed;

class AuthorizationServiceTest extends \Codeception\Test\Unit
{
    protected UnitTester $tester;
    protected AuthorizationService $authorizationService;

    protected function _before()
    {
        $this->authorizationService = $this->tester->getContainer()->get(AuthorizationService::class);
    }

    /**
     * Test the hasRole method
     *
     * @dataProvider hasRole
     *
     * @param int    $userId
     * @param string $role
     * @param bool   $expected
     *
     * @return void
     */
    public function testHasRole(int $userId, string $role, bool $expected): void
    {
        $actual = $this->authorizationService->hasRole($userId, $role);
        $this->assertSame($expected, $actual);
    }

    /**
     * Test the hasGroup method
     *
     * @dataProvider hasGroup
     *
     * @param int    $userId
     * @param string $group
     * @param bool   $expected
     *
     * @return void
     */
    public function testHasGroup(int $userId, string $group, bool $expected): void
    {
        $actual = $this->authorizationService->hasGroup($userId, $group);
        $this->assertSame($expected, $actual);
    }

    /**
     * Data provider for testHasRole
     *
     * @return array
     */
    public function hasRole(): array
    {
        require_once __DIR__ . '/../../../../resources/seeds/UserSeed.php';

        // directly = assigned as role
        // indirectly = assigned via group
        return [
            Role::ADMIN . ' for admin user (directly)' => [
                UserSeed::USER['admin'],
                Role::ADMIN,
                true,
            ],
            Role::MONITORING_QUEUE . ' for security_admin user (indirectly)' => [
                UserSeed::USER['security_admin'],
                Role::MONITORING_QUEUE,
                true,
            ],
            Role::SECURITY_ADMIN . ' for admin user' => [
                UserSeed::USER['admin'],
                Role::SECURITY_ADMIN,
                false,
            ],
            Role::SECURITY_ADMIN . ' for inexistent user id 250' => [
                UserSeed::USER['admin'],
                Role::SECURITY_ADMIN,
                false,
            ],
        ];
    }
    /**
     * Data provider for testHasRole
     *
     * @return array
     */
    public function hasGroup(): array
    {
        require_once __DIR__ . '/../../../../resources/seeds/UserSeed.php';

        // directly = assigned as role
        // indirectly = assigned via group
        return [
            Group::ADMIN . ' for admin user' => [
                UserSeed::USER['admin'],
                Group::ADMIN,
                true,
            ],
            Group::USER . ' for admin user' => [
                UserSeed::USER['admin'],
                Group::USER,
                false,
            ],
            Group::SECURITY_ADMIN . ' for inexistent user id 250' => [
                250,
                Group::SECURITY_ADMIN,
                false,
            ],
        ];
    }
}
