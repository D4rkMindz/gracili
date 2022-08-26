<?php

namespace Test\Api;

use ApiTester;

/**
 * Class ApiCest
 */
class ApiCest
{
    //
    //  NOTICE: This test does NOTHING
    //  This test only exists because otherwise, codeception would fail (requires a root level test in each group)
    //
    /**
     * Before hook.
     *
     * @param ApiTester $I
     */
    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
    }

    /**
     * After hook.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function _after(ApiTester $I)
    {
        $I->seeResponseJsonMatchesJsonPath('$.message');
        $I->seeResponseJsonMatchesJsonPath('$.success');
    }
}