<?php

declare(strict_types=1);

namespace App\User\Controller;

use App\User\Exception\EmailAlreadyUsedException;
use App\User\Redlist\RedlistClient;
use App\User\Service\LoginWithRedlist;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientException;

final class RedlistLoginController extends AbstractController
{
    private const string COOKIE = 'redlist_oauth';
    private const string COOKIE_PATH = '/api/auth/redlist';

    #[Route('/auth/redlist', name: 'auth_redlist', methods: ['GET'])]
    public function start(RedlistClient $redlist): RedirectResponse
    {
        $state = bin2hex(random_bytes(16));
        $codeVerifier = bin2hex(random_bytes(32));

        // The state and the PKCE verifier must survive the round trip to
        // Redlist, and the API has no session: they travel in a short-lived
        // cookie that only this flow can read.
        $response = new RedirectResponse($redlist->authorizeUrl($state, $codeVerifier));
        $response->headers->setCookie(
            Cookie::create(self::COOKIE, $state.'.'.$codeVerifier)
                ->withExpires(new \DateTimeImmutable('+10 minutes'))
                ->withPath(self::COOKIE_PATH)
                ->withSecure(true)
                ->withHttpOnly(true)
                ->withSameSite(Cookie::SAMESITE_LAX),
        );

        return $response;
    }

    #[Route('/auth/redlist/callback', name: 'auth_redlist_callback', methods: ['GET'])]
    public function callback(
        Request $request,
        RedlistClient $redlist,
        LoginWithRedlist $loginWithRedlist,
        AuthenticationSuccessHandler $successHandler,
    ): RedirectResponse {
        [$state, $codeVerifier] = explode('.', $request->cookies->getString(self::COOKIE), 2) + ['', ''];
        $code = $request->query->getString('code');

        $isValid = '' !== $state
            && '' !== $code
            && hash_equals($state, $request->query->getString('state'))
            && $redlist->issuer() === $request->query->getString('iss');

        if (!$isValid) {
            return $this->finish('/login?error=redlist');
        }

        try {
            $user = $loginWithRedlist($redlist->fetchIdentity($code, $codeVerifier));
        } catch (EmailAlreadyUsedException) {
            return $this->finish('/login?error=email_taken');
        } catch (HttpClientException) {
            return $this->finish('/login?error=redlist');
        }

        // Same cookies as a password login: the success handler builds the JWT
        // and lets the refresh token bundle attach its own cookie.
        $response = $this->finish('/');
        foreach ($successHandler->handleAuthenticationSuccess($user)->headers->getCookies() as $cookie) {
            $response->headers->setCookie($cookie);
        }

        return $response;
    }

    private function finish(string $path): RedirectResponse
    {
        $response = new RedirectResponse($path);
        $response->headers->clearCookie(self::COOKIE, self::COOKIE_PATH, null, true, true, Cookie::SAMESITE_LAX);

        return $response;
    }
}
