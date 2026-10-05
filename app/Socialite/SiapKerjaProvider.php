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

      
        if (isset($response['data']) && is_array($response['data'])) {
            $response = array_merge($response, $response['data']);
        }

        // Antisipasi jika key token bernama 'token' bukan 'access_token'
        if (empty($response['access_token']) && !empty($response['token'])) {
            $response['access_token'] = $response['token'];
        }

        \Illuminate\Support\Facades\Log::error('SIAPKerja token response metadata', [
            'response_keys' => is_array($response) ? array_keys($response) : [],
            'has_access_token' => !empty($response['access_token']),
            'token_type' => $response['token_type'] ?? null,
            'scope' => $response['scope'] ?? null,
        ]);

        return $response;
    }

    protected function getUserByToken($token): array
    {
        if (empty($token)) {
            \Illuminate\Support\Facades\Log::error('SIAPKerja: access_token kosong, tidak bisa ambil data user');
            throw new \RuntimeException('Access token dari SIAPKerja kosong. Cek client_id, client_secret, dan redirect_uri.');
        }

        $response = $this->getHttpClient()->get(
            'https://account.kemnaker.go.id/api/v1/users/me',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept'        => 'application/json',
                ],
            ]
        );

        $body = json_decode($response->getBody(), true);

        \Illuminate\Support\Facades\Log::info('SIAPKerja: getUserByToken response', [
            'status'      => $response->getStatusCode(),
            'has_data'    => isset($body['data']),
            'data_keys'   => isset($body['data']) ? array_keys($body['data']) : [],
        ]);

        return $body;
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
