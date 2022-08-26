<?php


namespace Test\Api\Group\Role;

require_once __DIR__ . '/../../../../resources/seeds/UserSeed.php';
require_once __DIR__ . '/../../../../resources/seeds/ACLSeed.php';

use ACLSeed;
use ApiTester;
use App\Service\ID\HashID;
use App\Type\Auth\Group;
use App\Type\Auth\Role;
use App\Type\HttpCode;
use Codeception\Example;
use UserSeed;

/**
 * Class GroupRolesViewAllCest
 */
class GroupRolesViewAllCest
{
    /**
     * Try to view groups roles
     *
     * @dataProvider viewAllRolesProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToViewAllRolesOfAGroup(ApiTester $I, Example $example)
    {
        $groupId = $example->offsetGet('id');
        $user = $example->offsetGet('user');
        $roles = $example->offsetGet('roles');
        $I->amJWTAuthenticated($user);
        $I->sendGet('/v1/groups/' . HashID::encode($groupId) . '/roles');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonMatchesJsonPath('$.roles');
        $response = $I->grabDataFromResponseByJsonPath('$')[0];
        $count = count($response['roles']);
        $I->seeResponseJsonHasValue('$.message', 'Found ' . $count . ' roles');
        $I->seeResponseJsonHasValue('$.count', $count);
        foreach ($roles as $role) {
            $I->seeResponseJsonHasValue('$.roles[*].id', $role['id']);
        }
    }

    /**
     * Try to view groups roles
     *
     * @dataProvider viewAllRolesUnauthorizedProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToViewAllRolesOfAGroupUnauthorized(ApiTester $I, Example $example)
    {
        $groupId = $example->offsetGet('id');
        $user = $example->offsetGet('user');
        $I->amJWTAuthenticated($user);
        $I->sendGet('/v1/groups/' . HashID::encode($groupId) . '/roles');
        $I->seeNotAuthorized();
    }

    /**
     * View all roles data provider
     *
     * @return array|array[]
     */
    protected function viewAllRolesProvider(): array
    {
        $data = [
            UserSeed::SECURITY_ADMIN . ' viewing ' . Group::USER => [
                'id' => ACLSeed::GROUP_ID[Group::USER],
                'user' => UserSeed::SECURITY_ADMIN,
                'roles' => [
                    ['id' => HashID::encode(ACLSeed::ROLE_ID[Role::USER])],
                ],
            ],
            UserSeed::SECURITY_ADMIN . ' viewing ' . Group::SECURITY_ADMIN => [
                'id' => ACLSeed::GROUP_ID[Group::SECURITY_ADMIN],
                'user' => UserSeed::SECURITY_ADMIN,
                'roles' => [],
            ],
        ];

        foreach (ACLSeed::ROLE_ID as $roleId) {
            $data[UserSeed::SECURITY_ADMIN . ' viewing ' . Group::SECURITY_ADMIN]['roles'][] = ['id' => HashID::encode($roleId)];
        }

        return $data;
    }

    /**
     * View all roles unauthorized data provider
     *
     * @return array
     */
    protected function viewAllRolesUnauthorizedProvider(): array
    {
        return [
            UserSeed::USER . ' trying to view ' . Group::USER . '\'s roles' => [
                'id' => ACLSeed::GROUP_ID[Group::USER],
                'user' => UserSeed::USER,
            ],
            UserSeed::ADMIN . ' trying to view ' . Group::USER . '\'s roles' => [
                'id' => ACLSeed::GROUP_ID[Group::USER],
                'user' => UserSeed::ADMIN,
            ],
        ];
    }
}
