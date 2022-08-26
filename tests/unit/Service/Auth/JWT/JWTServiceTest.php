<?php


namespace Unit\Service\Auth\JWT;

use App\Service\Auth\JWT\JWTService;
use App\Service\ID\HashID;
use App\Service\SettingsInterface;
use App\Type\Auth\Group;
use App\Type\Language;
use Firebase\JWT\JWT;
use UnitTester;

class JWTServiceTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;
    protected JWTService $jwtService;

    /**
     * Test the generation of a JWT
     *
     * @return void
     */
    public function testGenrateJWT()
    {
        $userId = 1;

        $token = $this->jwtService->generateJWT($userId);
        $jwt = json_decode(base64_decode(explode('.', $token)[1]));
        $settings = $this->tester->getContainer()->get(SettingsInterface::class)->get(JWT::class);
        $this->tester->assertSame($settings['issuer'], $jwt->iss); // issuer
        $this->tester->assertSame($settings['audience'], $jwt->aud); // audience
        // issued at
        $this->tester->assertLessThanOrEqual(time(), $jwt->iat);
        $this->tester->assertGreaterThanOrEqual(time() - 10, $jwt->iat);
        // not before
        $this->tester->assertLessThanOrEqual(time(), $jwt->nbf);
        $this->tester->assertGreaterThanOrEqual(time() - 10, $jwt->nbf);

        // expires
        $this->tester->assertLessThanOrEqual(time() + $settings['expire'], $jwt->exp);
        $this->tester->assertGreaterThanOrEqual(time() + $settings['expire'] - 10, $jwt->exp);

        // convert it to an array
        $data = json_decode(json_encode($jwt->data), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame(HashID::encode($userId), $data['id']);
        $this->assertArrayHasKey('last_login', $data);
        $this->assertArrayHasKey('roles', $data);
        $this->assertArrayHasKey('groups', $data);
        $this->assertSame(Group::SECURITY_ADMIN, $data['groups'][0]);
        $this->assertArrayHasKey('locale', $data);
        $this->assertSame(Language::EN_GB, $data['locale']);
    }

    protected function _before()
    {
        $this->jwtService = $this->tester->getContainer()->get(JWTService::class);
    }
}
