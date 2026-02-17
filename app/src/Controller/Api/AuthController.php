<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\DTO\LoginInput;
use App\Domain\Entity\User;
use App\Infrastructure\Repository\UserRepository;
use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\UnencryptedToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    /**
     * @param UserRepository $userRepository
     * @param UserPasswordHasherInterface $passwordHasher
     * @param string $jwtKey
     */
    public function __construct(
        private readonly UserRepository              $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly string                      $jwtKey

    )
    {
    }

    /**
     * @param LoginInput $input
     * @return JsonResponse
     */
    #[Route('/login',
        name: 'api_login',
        methods: ['POST']
    )]
    public function login(
        #[MapRequestPayload] LoginInput $input
    ): JsonResponse
    {
        $user = $this->userRepository->findByLogin($input->login);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $input->password)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Invalid credentials');
        }

        // Конфіг для JWT
        $jwt = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($this->jwtKey)
        );

        if(!empty($user->getApiToken())) {
            $token = $jwt->parser()->parse($user->getApiToken());
            assert($token instanceof UnencryptedToken);

            // Перевіряємо час дії токена
            $time = new DateTimeImmutable();
            if ($token->isExpired($time)) {
                // Токен прострочений — генеруємо новий
                $token = $this->generateJwtToken($user, $jwt);

                $user->setApiToken($token->toString());
                $this->userRepository->saveAndFlush($user);
            }
        } else {
            $token = $this->generateJwtToken($user, $jwt);

            $user->setApiToken($token->toString());
            $this->userRepository->saveAndFlush($user);
        }

        $responseData = [
            'login' => $user->getLogin(),
            'token' => $user->getApiToken()
        ];

        return new JsonResponse($responseData, Response::HTTP_OK);
    }

    /**
     * Генерація JWT токену
     * @param User $user
     * @param Configuration $jwt
     * @return UnencryptedToken
     */
    private function generateJwtToken(User $user, Configuration $jwt): UnencryptedToken
    {
        $time = new DateTimeImmutable();

        return $jwt->builder()
            ->identifiedBy((string)$user->getId())
            ->issuedAt($time)
            ->expiresAt($time->modify('+24 hour'))
            ->withClaim('login', $user->getLogin())
            ->withClaim('phone', $user->getPhone()->asString())
            ->withClaim('roles', $user->getRoles())
            ->getToken($jwt->signer(), $jwt->signingKey());
    }
}
