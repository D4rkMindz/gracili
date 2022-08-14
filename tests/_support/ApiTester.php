<?php

use App\Service\Auth\JWT\JWTService;
use App\Service\User\UserService;
use App\Type\HttpCode;
use Psr\Container\ContainerInterface;


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
 */
class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;

    /**
     * Check the response if it contains a specific value at a given path
     *
     * @param string $jsonPath
     * @param        $expected
     *
     * @return void
     */
    public function seeResponseJsonHasValue(string $jsonPath, $expected)
    {
        $actual = $this->grabDataFromResponseByJsonPath($jsonPath);
        if (isset($actual[0])) {
            $actual = $actual[0];
        } else {
            $this->assertTrue(false, "Response JSON does not have any value at JSON path " . $jsonPath);
        }
        $this->assertSame($expected, $actual);
    }

    /**
     * Verify that the login request failed and that it contains the expected data
     *
     * @return void
     */
    public function seeNotAuthorized()
    {
        $this->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
        $this->seeResponseIsJson();
        $this->seeResponseJsonHasValue('$.success', false);
        $this->seeResponseJsonHasValue('$.message', 'Authentication failed');
        $this->seeResponseJsonHasValue('$.error.message', 'Not authorized');
        $this->seeResponseJsonHasValue('$.error.fields[0].field', 'username');
        $this->seeResponseJsonHasValue('$.error.fields[0].message', 'Not authorized');
        $this->dontSeeResponseJsonMatchesJsonPath('$.error.fields[1]');
    }

    /**
     * I am JWT Authenticated.
     *
     * @param string $username
     *
     * @return void
     */
    public function amJWTAuthenticated(string $username)
    {
        /** @var UserService $userService */
        $userService = $this->getContainer()->get(UserService::class);
        $userId = $userService->getIdByUsername($username);

        /** @var JWTService $jwtService */
        $jwtService = $this->getContainer()->get(JWTService::class);
        $jwt = $jwtService->generateJWT($userId);
        $this->haveHttpHeader('Authorization', 'Bearer ' . $jwt);
    }

    /**
     * I am using the language <string>
     *
     * @param string $languageTag
     *
     * @return void
     */
    public function amUsingLanguage(string $languageTag)
    {
        $this->haveHttpHeader('X-Your-App-Language', $languageTag);
    }

    /**
     * Get the container
     *
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        $app = require __DIR__ . '/../../config/bootstrap.php';

        return $app->getContainer();
    }
}
