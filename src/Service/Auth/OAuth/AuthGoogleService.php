<?php

namespace App\Service\Auth\OAuth;

use App\Exception\OAuthException;
use App\Type\HttpCode;
use App\Util\ArrayReader;
use Google\Client;

class AuthGoogleService
{
    private Client $client;

    /**
     * Constructor
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get the authentication redirect URL
     *
     * @return string
     */
    public function getRedirectUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Get the user data from the oauth code
     *
     * @param string $code
     *
     * @return ArrayReader
     */
    public function getUserDataFromCode(string $code): ArrayReader
    {
        $data = $this->client->fetchAccessTokenWithAuthCode($code);
        $verifiedData = $this->client->verifyIdToken($data['id_token']);
        if (!$verifiedData) {
            throw new OAuthException(__('Invalid request'), HttpCode::UNAUTHORIZED);
        }
        $verifiedData['token_information'] = $data;

        return new ArrayReader($verifiedData);

        /**
         * {
         * "iss": "https:\/\/accounts.google.com",
         * "azp": "359867448098-qri6jqso21oe3o1foka2e10ur6bqv7kr.apps.googleusercontent.com",
         * "aud": "359867448098-qri6jqso21oe3o1foka2e10ur6bqv7kr.apps.googleusercontent.com",
         * "sub": "106322476980139946281",
         * "email": "bjoern.pfoster@gmail.com",
         * "email_verified": true,
         * "at_hash": "TwQYnF5HVoxnafrBcpBIUA",
         * "name": "Bj\u00f6rn Pfoster",
         * "picture": "https:\/\/lh3.googleusercontent.com\/a-\/AFdZucrn2yY0zzBF9Zt7Ohx3Z-AtLmnedEBd9U-pmhyviu0=s96-c",
         * "given_name": "Bj\u00f6rn",
         * "family_name": "Pfoster",
         * "locale": "en-GB",
         * "iat": 1660595291,
         * "exp": 1660598891
         * }
         */
    }
}