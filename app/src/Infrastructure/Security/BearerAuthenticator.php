<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Infrastructure\Repository\UserRepository;
use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\UnencryptedToken;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class BearerAuthenticator extends AbstractAuthenticator
{
    /**
     * @param UserRepository $userRepository
     * @param string $jwtKey
     */
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly string         $jwtKey
    )
    {
    }

    /**
     * @param Request $request
     * @return bool|null
     */
    public function supports(Request $request): ?bool
    {
        return str_starts_with((string)$request->headers->get('Authorization'), 'Bearer ');
    }

    /**
     * @param Request $request
     * @return SelfValidatingPassport
     */
    public function authenticate(Request $request): Passport
    {
        $header = $request->headers->get('Authorization');
        $token = str_replace('Bearer ', '', $header);

        $user = $this->userRepository->findByApiToken($token);

        if (!$user) {
            throw new AuthenticationException('Invalid API token');
        }

        // Конфіг для JWT
        $jwt = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($this->jwtKey)
        );

        $token = $jwt->parser()->parse($token);
        assert($token instanceof UnencryptedToken);

        // Перевіряємо час дії токена
        $time = new DateTimeImmutable();
        if ($token->isExpired($time)) {
            // Токен прострочений — генеруємо новий
            $newToken = $jwt->builder()
                ->identifiedBy((string)$user->getId())
                ->issuedAt($time)
                ->expiresAt($time->modify('+24 hour'))
                ->withClaim('login', $user->getLogin())
                ->withClaim('phone', $user->getPhone()->asString())
                ->withClaim('roles', $user->getRoles())
                ->getToken($jwt->signer(), $jwt->signingKey());

            $user->setApiToken($newToken->toString());
            $this->userRepository->saveAndFlush($user);

            // Передаємо новий токен фронтенду
            $request->attributes->set('new_api_token', $newToken->toString());
        }

        return new SelfValidatingPassport(
            new UserBadge($user->getPhone()->asString(), fn() => $user)
        );
    }


    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $firewallName
     * @return Response|null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return Response|null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
    }
}
