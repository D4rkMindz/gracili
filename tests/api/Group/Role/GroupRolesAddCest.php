<?php


namespace Test\Api\Group\Role;

require_once __DIR__ . '/../../../../resources/seeds/UserSeed.php';
require_once __DIR__ . '/../../../../resources/seeds/ACLSeed.php';

use ACLSeed;
use ApiTester;
use App\Service\ID\HashID;
use App\Table\GroupHasRoleTable;
use App\Type\Auth\Group;
use App\Type\Auth\Role;
use App\Type\HttpCode;
use Codeception\Example;
use UserSeed;

class GroupRolesAddCest
{
    /**
     * Try to view groups roles
     *
     * @dataProvider addRoleProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToAddRoleToAGroup(ApiTester $I, Example $example)
    {
        $groupId = $example->offsetGet('id');
        $user = $example->offsetGet('user');
        $roleId = $example->offsetGet('role');
        $I->amJWTAuthenticated($user);
        $I->sendPost('/v1/groups/' . HashID::encode($groupId) . '/roles/' . HashID::encode($roleId));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonHasValue('$.message', 'Successfully assigned role to group');
        $I->seeResponseJsonMatchesJsonPath('$.group');
        $I->seeResponseJsonHasValue('$.group.id', HashID::encode($groupId));

        $I->seeInDatabaseExcludingArchived(GroupHasRoleTable::getName(), [
            'group_id' => $groupId,
            'role_id' => $roleId,
        ]);
    }

    /**
     * Try to view groups roles
     *
     * @dataProvider addRoleUnauthorizedProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToAddRoleToAGroupUnauthorized(ApiTester $I, Example $example)
    {
        $groupId = $example->offsetGet('id');
        $user = $example->offsetGet('user');
        $roleId = $example->offsetGet('role');
        $I->amJWTAuthenticated($user);
        $I->sendPost('/v1/groups/' . HashID::encode($groupId) . '/roles/' . HashID::encode($roleId));
        $I->seeNotAuthorized();
    }

    /**
     * Try to add a role to a group that already has that role assigned
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function tryToAddRoleToAGroupThatAlreadyHasRole(ApiTester $I)
    {
        $I->amJWTAuthenticated(UserSeed::SECURITY_ADMIN);

        $groupId = ACLSeed::GROUP_ID[Group::ADMIN];
        $roleId = ACLSeed::ROLE_ID[Role::ADMIN];

        $I->seeInDatabaseExcludingArchived(GroupHasRoleTable::getName(), [
            'group_id' => $groupId,
            'role_id' => $roleId,
        ]);
        $I->sendPost('/v1/groups/' . HashID::encode($groupId) . '/roles/' . HashID::encode($roleId));
        $I->seeValidationErrors('Please check your data', [
            [
                'field' => 'role',
                'message' => 'Role already assigned to group',
            ],
        ]);
        $I->seeInDatabaseExcludingArchived(GroupHasRoleTable::getName(), [
            'group_id' => $groupId,
            'role_id' => $roleId,
        ]);
    }

    /**
     * Try to add a role to a group and some part is not found
     *
     * @dataProvider addRoleNotFoundProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToAddRoleToAGroupThatDoesNotExist(ApiTester $I, Example $example)
    {
        $I->amJWTAuthenticated(UserSeed::SECURITY_ADMIN);

        $groupId = $example->offsetGet('group_id');
        $roleId = $example->offsetGet('role_id');
        $errorMessage = $example->offsetGet('error_message');

        $I->dontSeeInDatabaseExcludingArchived(GroupHasRoleTable::getName(), [
            'group_id' => $groupId,
            'role_id' => $roleId,
        ]);
        $I->sendPost('/v1/groups/' . HashID::encode($groupId) . '/roles/' . HashID::encode($roleId));
        $I->seeNotFound($errorMessage);
        $I->dontSeeInDatabaseExcludingArchived(GroupHasRoleTable::getName(), [
            'group_id' => $groupId,
            'role_id' => $roleId,
        ]);
    }

    /**
     * Add a role to a group data provider
     *
     * @return array|array[]
     */
    protected function addRoleProvider(): array
    {
        return [
            UserSeed::SECURITY_ADMIN . ' adding ' . Role::ADMIN . ' to ' . Group::USER => [
                'user' => UserSeed::SECURITY_ADMIN,
                'role' => ACLSeed::ROLE_ID[Role::ADMIN],
                'id' => ACLSeed::GROUP_ID[Group::USER],
            ],
            UserSeed::SECURITY_ADMIN . ' adding ' . Role::SECURITY_ADMIN . ' to ' . Group::ADMIN => [
                'user' => UserSeed::SECURITY_ADMIN,
                'role' => ACLSeed::ROLE_ID[Role::SECURITY_ADMIN],
                'id' => ACLSeed::GROUP_ID[Group::ADMIN],
            ],
        ];
    }

    /**
     * Add a role to a group as unauthorized group data provider
     *
     * @return array
     */
    protected function addRoleUnauthorizedProvider(): array
    {
        return [
            UserSeed::USER . ' trying to add ' . Role::ADMIN . ' to ' . Group::USER . '\'s roles' => [
                'user' => UserSeed::USER,
                'role' => ACLSeed::ROLE_ID[Role::ADMIN],
                'id' => ACLSeed::GROUP_ID[Group::USER],
            ],
            UserSeed::ADMIN . ' trying to add ' . Role::SECURITY_ADMIN . ' to ' . Group::ADMIN . '\'s roles' => [
                'user' => UserSeed::ADMIN,
                'role' => ACLSeed::ROLE_ID[Role::SECURITY_ADMIN],
                'id' => ACLSeed::GROUP_ID[Group::ADMIN],
            ],
        ];
    }

    /**
     * Add role not found provider
     *
     * @return array[]
     */
    protected function addRoleNotFoundProvider(): array
    {
        return [
            'Group not found' => [
                'group_id' => 999999999999999999,
                'role_id' => ACLSeed::ROLE_ID[Role::USER],
                'error_message' => 'Group not found',
            ],
            'Role not found' => [
                'group_id' => ACLSeed::GROUP_ID[Group::USER],
                'role_id' => 999999999999999999,
                'error_message' => 'Role not found',
            ],
        ];
    }
}
