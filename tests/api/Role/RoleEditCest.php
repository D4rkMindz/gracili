<?php

namespace Test\Api\Role;

require_once __DIR__ . '/../../../resources/seeds/UserSeed.php';
require_once __DIR__ . '/../../../resources/seeds/ACLSeed.php';

use ACLSeed;
use ApiTester;
use App\Service\ID\HashID;
use App\Table\RoleTable;
use App\Type\Auth\Role;
use App\Type\HttpCode;
use Codeception\Example;
use Moment\Moment;
use UserSeed;

class RoleEditCest
{
    /**
     * Test updating a user
     *
     * @dataProvider roleEditSuccessfulProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToUpdateARoleSuccessfully(ApiTester $I, Example $example)
    {
        $I->amJWTAuthenticated($example->offsetGet('user'));
        $id = $example->offsetGet('id');
        $I->sendPut('/v1/roles/' . HashID::encode($id), $example->offsetGet('body'));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonHasValue('$.message', 'Updated role successfully');
        $I->seeInDatabaseExcludingArchived(RoleTable::getName(),
            array_merge_recursive(['id' => $id], $example->offsetGet('verify')));
    }

    /**
     * Test updating a user
     *
     * @dataProvider roleEditUnsuccessfulProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToUpdateARoleUnsuccessfully(ApiTester $I, Example $example)
    {
        $I->amJWTAuthenticated($example->offsetGet('user'));
        $id = $example->offsetGet('id');
        $I->sendPut('/v1/roles/' . HashID::encode($id), $example->offsetGet('body'));
        $I->seeNotAuthorized();
    }


    /**
     * User edit (successfully) provider
     *
     * @return array[]
     */
    protected function roleEditSuccessfulProvider(): array
    {
        return [
            UserSeed::SECURITY_ADMIN . ' updating role.description' => [
                'id' => ACLSeed::ROLE_ID[Role::ADMIN],
                'user' => UserSeed::SECURITY_ADMIN,
                'body' => [
                    'description' => 'new description submitted for testing',
                ],
                'verify' => [
                    'description' => 'new description submitted for testing',
                    'modified_at >=' => (new Moment())->subtractMinutes(5)->format('Y-m-d H:i:s'),
                    'modified_by >=' => UserSeed::USER_ID[UserSeed::SECURITY_ADMIN],
                ],
            ],
        ];
    }

    /**
     * User edit unsuccessful data provider
     *
     * @return array[]
     */
    protected function roleEditUnsuccessfulProvider(): array
    {
        return [
            UserSeed::USER . ' updating role.name' => [
                'id' => ACLSeed::ROLE_ID[Role::SECURITY_ADMIN],
                'user' => UserSeed::USER,
                'body' => [
                    'description' => 'role.new_name',
                ],
            ],
            UserSeed::ADMIN . ' updating role.name' => [
                'id' => ACLSeed::ROLE_ID[Role::SECURITY_ADMIN],
                'user' => UserSeed::ADMIN,
                'body' => [
                    'description' => 'A new description',
                ],
            ],
        ];
    }
}
