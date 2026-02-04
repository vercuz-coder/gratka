<?php

namespace App\Security;

use App\Entity\AuthToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class TokenAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public const LOGIN_ROUTE = 'auth_login';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === self::LOGIN_ROUTE
            && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('username', '');
        $token = $request->request->get('token', '');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $username);

        if (!$username || !$token) {
             throw new CustomUserMessageAuthenticationException('Username and token are required.');
        }

        return new SelfValidatingPassport(
            new UserBadge($username, function ($userIdentifier) use ($token) {
                // Check if token exists and belongs to user
                $authToken = $this->entityManager->getRepository(AuthToken::class)->findOneBy(['token' => $token]);
                
                if (!$authToken) {
                    throw new CustomUserMessageAuthenticationException('Invalid credentials.');
                }
                
                $user = $authToken->getUser();
                
                if ($user->getUsername() !== $userIdentifier) {
                     throw new CustomUserMessageAuthenticationException('Invalid credentials.');
                }
                
                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->urlGenerator->generate('home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if ($request->hasSession()) {
            $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);
        }

        return new RedirectResponse($this->urlGenerator->generate(self::LOGIN_ROUTE));
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        $url = $this->urlGenerator->generate(self::LOGIN_ROUTE);
        return new RedirectResponse($url);
    }
}
