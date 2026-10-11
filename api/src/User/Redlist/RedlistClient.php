<?php

declare(strict_types=1);

namespace App\User\Redlist;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class RedlistClient
{
    public function __construct(
        private HttpClientInterface $http,
        #[Autowire(env: 'REDLIST_URL')]
        private string $redlistUrl,
        #[Autowire(env: 'REDLIST_CLIENT_ID')]
        private string $clientId,
        #[Autowire('%env(APP_URL)%/api/auth/redlist/callback')]
        private string $redirectUri,
    ) {
    }

    public function issuer(): string
    {
        return $this->redlistUrl;
    }

    public function authorizeUrl(string $state, string $codeVerifier): string
    {
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

        return $this->redlistUrl.'/oauth/authorize?'.http_build_query([
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => 'profile:read',
            'state' => $state,
            'code_challenge' => $challenge,
            'code_challenge_method' => 'S256',
        ]);
    }

    public function fetchIdentity(string $code, string $codeVerifier): RedlistIdentity
    {
        $token = $this->http->request('POST', $this->redlistUrl.'/api/oauth/token', [
            'body' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->redirectUri,
                'client_id' => $this->clientId,
                'code_verifier' => $codeVerifier,
            ],
            'timeout' => 5,
        ])->toArray();

        $user = $this->http->request('GET', $this->redlistUrl.'/api/oauth/userinfo', [
            'auth_bearer' => $token['access_token'],
            'timeout' => 5,
        ])->toArray();

        return new RedlistIdentity(Uuid::fromString($user['sub']), $user['email'], $user['pseudo']);
    }
}
