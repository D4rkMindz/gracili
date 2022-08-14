<?php

namespace Test\Api\Monitoring;

use ApiTester;
use App\Type\HttpCode;
use Codeception\Example;

class QueueCest
{
    public function _before(ApiTester $I)
    {
    }

    /**
     * Test if the correct running queue count is returned
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function tryToGetQueueCount(ApiTester $I)
    {
        $expectedCount = exec('sh ' . __DIR__ . '/../../../bin/enqueue/count.sh');

        $I->amJWTAuthenticated('security_admin');
        $I->sendGet('/v1/monitoring/queue');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonHasValue('$.success', true);
        $I->seeResponseJsonHasValue('$.message', 'Found ' . $expectedCount . ' running queue workers');
        $I->seeResponseJsonHasValue('$.count', $expectedCount);
    }

    /**
     * A regular user should NOT see this information
     *
     * @param ApiTester $I
     * @param Example   $example
     *
     * @return void
     *
     * @dataProvider unauthorizedUsersProvider
     */
    public function tryToGetQueueCountAsUnauthorizedUser(ApiTester $I, Example $example)
    {
        $I->amJWTAuthenticated($example[0]);
        $I->sendGet('/v1/monitoring/queue');
        $I->seeNotAuthorized();
    }

    protected function unauthorizedUsersProvider(): array
    {
        return [
            ['admin'],
            ['user'],
        ];
    }
}
