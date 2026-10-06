<?php

namespace App\Socialite;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;
use JsonException;
use RuntimeException;
use Laravel\Socialite\Two\InvalidStateException;

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
        $response = $this->postTokenRequest($this->getTokenFields($code));

        Log::info('SIAPKerja token response metadata', [
            'response_keys' => is_array($response) ? array_keys($response) : [],
            'has_access_token' => !empty($response['access_token']),
            'token_type' => $response['token_type'] ?? null,
            'scope' => $response['scope'] ?? null,
        ]);

        return $response;
    }

    protected function getRefreshTokenResponse($refreshToken)
    {
        return $this->postTokenRequest([
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);
    }

    public function user()
    {
        if ($this->hasInvalidState()) {
            throw new InvalidStateException;
        }

        $tokenResponse = $this->getAccessTokenResponse($this->getCode());

        try {
            $user = $this->getUserByToken($tokenResponse['access_token']);
        } catch (ClientException $exception) {
            if (
                $exception->getResponse()?->getStatusCode() !== 401
                || empty($tokenResponse['refresh_token'])
            ) {
                throw $exception;
            }

            Log::warning('SIAPKerja rejected the access token; attempting one refresh');

            $tokenResponse = $this->getRefreshTokenResponse($tokenResponse['refresh_token']);
            $user = $this->getUserByToken($tokenResponse['access_token']);
        }

        return $this->userInstance($tokenResponse, $user);
    }

    private function postTokenRequest(array $fields): array
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            RequestOptions::JSON => $fields,
        ]);

        try {
            $body = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Respons token SIAPKerja bukan JSON yang valid.', previous: $exception);
        }

        if (!is_array($body)) {
            throw new RuntimeException('Respons token SIAPKerja memiliki format yang tidak valid.');
        }

        if (isset($body['data']) && is_array($body['data'])) {
            $body = array_merge($body, $body['data']);
        }

        if (empty($body['access_token']) && !empty($body['token'])) {
            $body['access_token'] = $body['token'];
        }

        if (empty($body['access_token'])) {
            Log::error('SIAPKerja token response did not contain an access token', [
                'response_keys' => array_keys($body),
            ]);

            throw new RuntimeException('Respons token SIAPKerja tidak berisi access_token.');
        }

        return $body;
    }

    protected function getUserByToken($token): array
    {
        if (empty($token)) {
            Log::error('SIAPKerja: access_token kosong, tidak bisa ambil data user');
            throw new RuntimeException('Access token dari SIAPKerja kosong. Cek respons token SIAPKerja.');
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

        Log::info('SIAPKerja: getUserByToken response', [
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
