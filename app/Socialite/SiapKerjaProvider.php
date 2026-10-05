<?php

namespace App\Socialite;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class SiapKerjaProvider extends AbstractProvider implements ProviderInterface
{
    protected $scopes = ['basic', 'email'];

    protected $scopeSeparator = ' ';

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase(
            'https://account.kemnaker.go.id/auth',
            $state
        );
    }

    protected function getTokenUrl(): string
    {
        return 'https://account.kemnaker.go.id/api/v1/tokens';
    }

    public function getAccessTokenResponse($code)
    {
        $response = parent::getAccessTokenResponse($code);

        \Illuminate\Support\Facades\Log::warning('SIAPKerja token response metadata', [
            'response_keys' => is_array($response) ? array_keys($response) : [],
            'has_access_token' => is_array($response)
                && isset($response['access_token'])
                && is_string($response['access_token'])
                && $response['access_token'] !== '',
            'token_type' => is_array($response) ? ($response['token_type'] ?? null) : null,
            'scope' => is_array($response) ? ($response['scope'] ?? null) : null,
        ]);

        return $response;
    }

    protected function getUserByToken($token): array
    {
        $response = $this->getHttpClient()->get(
            'https://account.kemnaker.go.id/api/v1/users/me',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
            ]
        );

        return json_decode($response->getBody(), true);
    }

    protected function mapUserToObject(array $user): User
    {
        return (new User())->setRaw($user)->map([
            'id'    => $user['data']['id'] ?? null,
            'name'  => $user['data']['name'] ?? null,
            'email' => $user['data']['email'] ?? null,
        ]);
    }

    protected function getTokenFields($code): array
    {
        return [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $this->redirectUrl,
        ];
    }
}
