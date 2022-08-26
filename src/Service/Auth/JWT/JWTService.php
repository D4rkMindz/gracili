<?php

namespace App\Service\Auth\JWT;

use App\Exception\AuthenticationException;
use App\Repository\GroupRepository;
use App\Repository\JWTRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Service\ID\HashID;
use App\Service\SettingsInterface;
use App\Type\HttpCode;
use DateTimeInterface;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Moment\Moment;

/**
 * Class JWTService
 */
class JWTService
{
    private array $config;
    private UserRepository $userRepository;
    private RoleRepository $roleRepository;
    private GroupRepository $groupRepository;
    private JWTRepository $jwtRepository;

    /**
     * Constructor
     */
    public function __construct(
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        GroupRepository $groupRepository,
        JWTRepository $jwtRepository,
        SettingsInterface $settings
    ) {
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
        $this->groupRepository = $groupRepository;
        $this->jwtRepository = $jwtRepository;
        $this->config = $settings->get(JWT::class);
    }

    /**
     * Generate a JWT for a user
     *
     * @param int $userId
     *
     * @return string
     */
    public function generateJWT(int $userId): string
    {
        $now = new Moment();
        $lastLogin = $now;
        $lastReportedLogin = $this->userRepository->getLastLogin($userId);
        if (!empty($lastReportedLogin)) {
            $lastLogin = $lastReportedLogin;
        }
        $this->userRepository->setLastLogin($userId, $now->format('Y-m-d H:i:s'), $userId);

        $roles = $this->roleRepository->findAssignedRoles($userId);
        $roles = array_map(fn($role) => $role['name'], $roles);
        $groups = $this->groupRepository->findAssignedGroups($userId);
        $groups = array_map(fn($group) => $group['name'], $groups);

        $locale = $this->userRepository->getLanguageTag($userId);

        $issuerClaim = $this->config['issuer']; // this can be the servername
        $audienceClaim = $this->config['audience'];
        $issuedAtClaim = time(); // issued at
        $notBeforeClaim = $issuedAtClaim; //not before in seconds
        $expireClaim = $issuedAtClaim + $this->config['expire']; // expire time in seconds
        $tokenData = [
            'iss' => $issuerClaim,
            'aud' => $audienceClaim,
            'iat' => $issuedAtClaim,
            'nbf' => $notBeforeClaim,
            'exp' => $expireClaim,
            'data' => [
                JWTData::USER_HASH => HashID::encode($userId),
                JWTData::LAST_LOGIN => $lastLogin instanceof DateTimeInterface ? $lastLogin->format(DATE_ATOM) : null,
                JWTData::ROLES => $roles,
                JWTData::GROUPS => $groups,
                JWTData::LOCALE => $locale,
            ],
        ];

        $secretKey = $this->extractPrivateKeyFromFile(
            $this->config['secret_file']['path'],
            $this->config['secret_file']['password']
        );

        return JWT::encode($tokenData, $secretKey, $this->config['algorithm'][0]);
    }

    /**
     * Extract a private key from a file
     *
     * @param string $file
     * @param string $password
     *
     * @return string
     */
    public function extractPrivateKeyFromFile(string $file, string $password): string
    {
        $content = file_get_contents($file);
        $privateKey = openssl_pkey_get_private($content, $password);
        $key = null;
        openssl_pkey_export($privateKey, $key);
        $key = str_replace('-----END PRIVATE KEY-----', '', $key);
        $key = str_replace('-----BEGIN PRIVATE KEY-----', '', $key);

        return trim($key);
    }

    /**
     * Create a refresh token based on the WJT
     *
     * @param string $jwt
     *
     * @return string
     */
    public function createRefreshToken(string $jwt): string
    {
        $decoded = explode('.', $jwt);
        $data = json_decode(base64_decode($decoded[1]), true);
        $userId = HashID::decodeSingle($data['data'][JWTData::USER_HASH]);
        $issuedAt = new Moment('@' . $data['iat']);
        $expiredAt = new Moment('@' . $data['exp']);
        $refreshToken = HashID::encode([$userId, $data['iat'], $data['exp'], time()]);
        $this->jwtRepository->saveJWTToken($userId, $jwt, $refreshToken, $issuedAt, $expiredAt, $userId);

        return $refreshToken;
    }

    /**
     * Check if a JWT token is still valid (not expired)
     *
     * @param string $token
     *
     * @return bool
     */
    public function isValid(string $token): bool
    {
        try {
            $decoded = $this->decodeJWT($token);

            return $decoded['exp'] > time();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Decode a jwt token
     *
     * @param string $token
     *
     * @return array
     */
    public function decodeJWT(string $token)
    {
        $secretKey = $this->extractPrivateKeyFromFile(
            $this->config['secret_file']['path'],
            $this->config['secret_file']['password']
        );
        try {
            $decoded = JWT::decode(
                $token,
                new Key($secretKey, $this->config['algorithm'][0])
            );

            return (array)$decoded;
        } catch (ExpiredException $exp) {
            throw new AuthenticationException(HttpCode::UNAUTHORIZED, __('Token expired'), 0, $exp);
        } catch (Exception $e) {
            throw new AuthenticationException(HttpCode::UNAUTHORIZED, __('Not authorized'), 0, $e);
        }
    }
}