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
     * Get the container
     *
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        $app = require __DIR__ . '/../../config/bootstrap.php';

        return $app->getContainer();
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
     * Check the response if it contains a specific value at a given path
     *
     * @param string $jsonPath
     * @param        $expected
     *
     * @return void
     */
    public function dontSeeResponseJsonHasValue(string $jsonPath, $expected)
    {
        $found = $this->grabDataFromResponseByJsonPath($jsonPath);
        $this->assertNotEmpty($found, "Response JSON does not have any value at JSON path " . $jsonPath);
        foreach ($found as $actual) {
            $this->assertNotSame($expected, $actual);
        }
    }

    /**
     * Check if record is found (excluding archived records automatically)
     *
     * @param string $table
     * @param array  $criteria
     *
     * @return void
     */
    public function seeInDatabaseExcludingArchived(string $table, array $criteria)
    {
        $criteria['archived_at'] = null;
        $this->seeInDatabase($table, $criteria);
    }


    /**
     * Check if record is surely NOT found (excluding archived records automatically)
     *
     * @param string $table
     * @param array  $criteria
     *
     * @return void
     */
    public function dontSeeInDatabaseExcludingArchived(string $table, array $criteria)
    {
        $criteria['archived_at'] = null;
        $this->dontSeeInDatabase($table, $criteria);
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
        $this->seeResponseJsonHasValue('$.message', 'Not authorized');
        $this->seeResponseJsonHasValue('$.error_type', 'not_authorized');
        $this->seeResponseJsonHasValue('$.error.message', 'Not authorized');
        $this->seeResponseJsonHasValue('$.error.fields[0].field', 'username');
        $this->seeResponseJsonHasValue('$.error.fields[0].message', 'Not authorized');
        $this->dontSeeResponseJsonMatchesJsonPath('$.error.fields[1]');
    }

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
        $found = $this->grabDataFromResponseByJsonPath($jsonPath);
        $this->assertNotEmpty($found, "Response JSON does not have any value at JSON path " . $jsonPath);
        $this->assertContains($expected, $found);
    }

    /**
     * Expect a validation error
     *
     * @param string|null $expectedMessage
     * @param array|null  $expectedErrors
     *
     * @return void
     */
    public function seeValidationErrors(?string $expectedMessage, ?array $expectedErrors)
    {
        $expectedMessage = $expectedMessage ?: 'Please check your data';
        $this->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
        $this->seeResponseIsJson();
        $this->seeResponseJsonHasValue('$.success', false);
        $this->seeResponseJsonHasValue('$.message', $expectedMessage);
        $this->seeResponseJsonHasValue('$.error.message', $expectedMessage);
        $this->seeResponseJsonHasValue('$.error_type', 'invalid_data');
        foreach ($expectedErrors as $expectedError) {
            $this->seeResponseJsonHasValue('$.error.errors[*].field', $expectedError['field']);
            $this->seeResponseJsonHasValue('$.error.errors[*].message', $expectedError['message']);
        }
    }

    /**
     * See that a record was not found
     *
     * @param string|null $expectedMessage
     *
     * @return void
     */
    public function seeNotFound(?string $expectedMessage)
    {
        $this->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $this->seeResponseIsJson();
        $this->seeResponseJsonHasValue('$.success', false);
        $this->seeResponseJsonHasValue('$.message', 'Not found');
        $this->seeResponseJsonHasValue('$.error_message', $expectedMessage);
        $this->seeResponseJsonHasValue('$.error_type', 'not_found');
    }
}
