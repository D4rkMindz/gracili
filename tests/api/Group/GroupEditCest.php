<?php

namespace Test\Api\Group;

require_once __DIR__ . '/../../../resources/seeds/UserSeed.php';
require_once __DIR__ . '/../../../resources/seeds/ACLSeed.php';

use ACLSeed;
use ApiTester;
use App\Service\ID\HashID;
use App\Table\GroupTable;
use App\Type\Auth\Group;
use App\Type\HttpCode;
use Codeception\Example;
use Moment\Moment;
use UserSeed;

class GroupEditCest
{
    /**
     * Test updating a user
     *
     * @dataProvider groupEditSuccessfulProvider
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     */
    public function tryToUpdateAGroupSuccessfully(ApiTester $I, Example $example)
    {
        $I->amJWTAuthenticated($example->offsetGet('user'));
        $id = $example->offsetGet('id');
        $I->sendPut('/v1/groups/' . HashID::encode($id), $example->offsetGet('body'));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonHasValue('$.message', 'Updated group successfully');
        $I->seeInDatabaseExcludingArchived(GroupTable::getName(),
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
    public function tryToUpdateAGroupUnsuccessfully(ApiTester $I, Example $example)
    {
        $I->amJWTAuthenticated($example->offsetGet('user'));
        $id = $example->offsetGet('id');
        $I->sendPut('/v1/groups/' . HashID::encode($id), $example->offsetGet('body'));
        $I->seeNotAuthorized();
    }


    /**
     * User edit (successfully) provider
     *
     * @return array[]
     */
    protected function groupEditSuccessfulProvider(): array
    {
        return [
            UserSeed::SECURITY_ADMIN . ' updating group.name' => [
                'id' => ACLSeed::GROUP_ID[Group::ADMIN],
                'user' => UserSeed::SECURITY_ADMIN,
                'body' => [
                    'name' => 'group.new_name',
                ],
                'verify' => [
                    'name' => 'group.new_name',
                    'modified_at >=' => (new Moment())->subtractMinutes(5)->format('Y-m-d H:i:s'),
                    'modified_by >=' => UserSeed::USER_ID[UserSeed::SECURITY_ADMIN],
                ],
            ],
            UserSeed::SECURITY_ADMIN . ' updating group.description' => [
                'id' => ACLSeed::GROUP_ID[Group::ADMIN],
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
    protected function userEditUnsuccessfulProvider(): array
    {
        return [
            UserSeed::USER . ' updating group.name' => [
                'id' => ACLSeed::GROUP_ID[Group::SECURITY_ADMIN],
                'user' => UserSeed::USER,
                'body' => [
                    'name' => 'group.new_name',
                ],
            ],
            UserSeed::ADMIN . ' updating group.name' => [
                'id' => ACLSeed::GROUP_ID[Group::SECURITY_ADMIN],
                'user' => UserSeed::ADMIN,
                'body' => [
                    'name' => 'group.new_name',
                ],
            ],
        ];
    }
}
