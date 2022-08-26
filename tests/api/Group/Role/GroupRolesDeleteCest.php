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

class GroupRolesDeleteCest
{
    /**
     * Try to view groups roles
     *
     * @dataProvider removeRoleProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToRemoveARoleFromAGroup(ApiTester $I, Example $example)
    {
        $groupId = $example->offsetGet('id');
        $roleId = $example->offsetGet('role');
        $I->amJWTAuthenticated(UserSeed::SECURITY_ADMIN);
        $I->sendDelete('/v1/groups/' . HashID::encode($groupId) . '/roles/' . HashID::encode($roleId));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonHasValue('$.message', 'Successfully removed role from group');
        $I->seeResponseJsonMatchesJsonPath('$.group');
        $I->seeResponseJsonHasValue('$.group.id', HashID::encode($groupId));

        $I->dontSeeInDatabaseExcludingArchived(GroupHasRoleTable::getName(), [
            'group_id' => $groupId,
            'role_id' => $roleId,
        ]);
    }

    /**
     * Try to view groups roles
     *
     * @dataProvider removeRoleUnauthorizedProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToAddRoleToAGroupUnauthorized(ApiTester $I, Example $example)
    {
        $groupId = $example->offsetGet('id');
        $group = $example->offsetGet('group');
        $roleId = $example->offsetGet('role');
        $I->amJWTAuthenticated($group);
        $I->sendDelete('/v1/groups/' . HashID::encode($groupId) . '/roles/' . HashID::encode($roleId));
        $I->seeNotAuthorized();
    }

    /**
     * Try to add a role to a group that already has that role assigned
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function tryToRemoveARoleFromAGroupThatAlreadyDontHasRole(ApiTester $I)
    {
        $I->amJWTAuthenticated(UserSeed::SECURITY_ADMIN);

        $groupId = ACLSeed::GROUP_ID[Group::ADMIN];
        $roleId = ACLSeed::ROLE_ID[Role::SECURITY_ADMIN];

        $I->dontSeeInDatabaseExcludingArchived(GroupHasRoleTable::getName(), [
            'group_id' => $groupId,
            'role_id' => $roleId,
        ]);
        $I->sendDelete('/v1/groups/' . HashID::encode($groupId) . '/roles/' . HashID::encode($roleId));
        $I->seeValidationErrors('Please check your data', [
            [
                'field' => 'role',
                'message' => 'Role not assigned to group',
            ],
        ]);
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
    protected function removeRoleProvider(): array
    {
        return [
            UserSeed::SECURITY_ADMIN . ' removing ' . Role::ADMIN . ' from ' . Group::ADMIN => [
                'id' => ACLSeed::GROUP_ID[Group::ADMIN],
                'role' => ACLSeed::ROLE_ID[Role::ADMIN],
            ],
            UserSeed::SECURITY_ADMIN . ' removing ' . Role::USER . ' from ' . Group::USER => [
                'id' => ACLSeed::GROUP_ID[Group::USER],
                'role' => ACLSeed::ROLE_ID[Role::USER],
            ],
        ];
    }

    /**
     * Add a role to a group as unauthorized group data provider
     *
     * @return array
     */
    protected function removeRoleUnauthorizedProvider(): array
    {
        return [
            UserSeed::USER . ' trying to remove ' . Role::ADMIN . ' from ' . Group::ADMIN . '\'s roles' => [
                'id' => ACLSeed::GROUP_ID[Group::ADMIN],
                'group' => UserSeed::USER,
                'role' => ACLSeed::ROLE_ID[Role::ADMIN],
            ],
            UserSeed::ADMIN . ' trying to remove ' . Role::SECURITY_ADMIN . ' from ' . Group::SECURITY_ADMIN . '\'s roles' => [
                'id' => ACLSeed::GROUP_ID[Group::SECURITY_ADMIN],
                'group' => UserSeed::ADMIN,
                'role' => ACLSeed::ROLE_ID[Role::SECURITY_ADMIN],
            ],
        ];
    }
}
