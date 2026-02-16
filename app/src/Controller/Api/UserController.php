<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Application\Exception\AppException;
use App\Domain\Entity\User;
use App\Domain\ValueObject\MobilePhone;
use App\Infrastructure\Repository\UserRepository;
use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/users', name: 'api_users_')]
final readonly class UserController
{
    /**
     * @param UserRepository $userRepository
     * @param UserPasswordHasherInterface $passwordHasher
     * @param Security $security
     * @param string $jwtKey
     */
    public function __construct(
        private UserRepository              $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private Security                    $security,
        private string                      $jwtKey
    )
    {
    }

    /**
     * Додаємо нового користувача
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function createUser(
        Request $request
    ): JsonResponse
    {
        // Вхідні дані
        $data = json_decode($request->getContent(), true);

        // Валідуємо вхідні дані
        $validationError = $this->validateUserData($data);
        if ($validationError) {
            throw new BadRequestException($validationError);
        }

        $current = $this->security->getUser();
        if ($current->getRoles()[0] !== 'ROLE_ROOT') {
            // Звичайний користувач може бачити тільки себе
            $user = $this->userRepository->findByLoginAndPhone($data['login'], new MobilePhone($data['phone']));
            if (!$user) {
                throw new NotFoundHttpException('User not found');
            }

            if ($current->getId() !== $user->getId()) {
                throw new AccessDeniedHttpException('Access denied');
            }

            $data = [
                'id' => $user->getId(),
                'login' => $user->getLogin(),
                'phone' => $user->getPhone()->asString(),
                'password' => $user->getPassword(),
            ];

            return new JsonResponse($data, Response::HTTP_OK);
        }

        $user = new User();
        $user->setLogin($data['login']);
        $user->setPhone(new MobilePhone($data['phone']));
        $user->setRoles(['ROLE_USER']);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $jwt = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($this->jwtKey)
        );

        $time = new DateTimeImmutable();

        $token = $jwt->builder()
            ->identifiedBy((string)$user->getId())
            ->issuedAt($time)
            ->expiresAt($time->modify('+24 hour'))
            ->withClaim('login', $user->getLogin())
            ->withClaim('phone', $user->getPhone()->asString())
            ->withClaim('roles', $user->getRoles())
            ->getToken($jwt->signer(), $jwt->signingKey());

        $user->setApiToken($token->toString());

        try {
            $this->userRepository->saveAndFlush($user);
        } catch (AppException) {
            throw new BadRequestException('Duplicate user');
        }

        $data = [
            'id' => $user->getId(),
            'login' => $user->getLogin(),
            'phone' => $user->getPhone()->asString(),
            'password' => $user->getPassword(),
        ];

        return new JsonResponse($data, Response::HTTP_CREATED);
    }

    /**
     * Оновлення користувача
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('', name: 'update', methods: ['PUT'])]
    public function updateUser(
        Request $request
    ): JsonResponse
    {
        // Вхідні дані
        $data = json_decode($request->getContent(), true);

        // Валідація
        $validationError = $this->validateUserData($data);
        if ($validationError) {
            throw new BadRequestException($validationError);
        }

        // Звичайний користувач може оновлювати тільки себе
        $current = $this->security->getUser();
        if ($current->getRoles()[0] !== 'ROLE_ROOT') {
            $user = $this->userRepository->findByLoginAndPhone(
                $data['login'],
                new MobilePhone($data['phone'])
            );

            if (!$user) {
                throw new NotFoundHttpException('User not found');
            }

            if ($current->getId() !== $user->getId()) {
                throw new AccessDeniedHttpException('Access denied');
            }
        }

        $user = $this->userRepository->find($data['id']);
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        // Оновлюємо дані користувача
        $user->setLogin($data['login']);
        $user->setPhone(new MobilePhone($data['phone']));

        // Оновлюємо пароль, якщо переданий
        if (!empty($data['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        try {
            $this->userRepository->saveAndFlush($user);
        } catch (AppException) {
            throw new BadRequestException('Duplicate user');
        }

        // Повертаємо оновлені дані
        $responseData = [
            'id'       => $user->getId()
        ];

        return new JsonResponse($responseData, Response::HTTP_OK);
    }

    /**
     * Отримуємо користувача по ID
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'user', methods: ['GET'])]
    public function getUser(
        int $id
    ): JsonResponse
    {
        // Перевіряємо роль користувача
        $current = $this->security->getUser();
        if ($current->getRoles()[0] !== 'ROLE_ROOT' && $current->getId() !== $id) {
            throw new AccessDeniedHttpException('Access denied');
        }

        // Перевірка, чи користувач існує в базі даних
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $data = [
            'login' => $user->getLogin(),
            'phone' => $user->getPhone()->asString(),
            'password' => $user->getPassword(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * Видалення користувача
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function deleteUser(
        int $id
    ): JsonResponse
    {
        // Перевіряємо роль користувача
        $current = $this->security->getUser();
        if($current->getRoles()[0] !== 'ROLE_ROOT') {
            throw new AccessDeniedHttpException('Access denied');
        }

        // Перевірка, чи користувач якого хочемо видалити є в базі даних
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        // Перевірка, щоб не видалити самого себе
        if ($current->getId() === $user->getId()) {
            throw new AccessDeniedHttpException('You cannot delete yourself');
        }

        // Видаляємо користувача
        $this->userRepository->deleteAndFlash($user);

        return new JsonResponse(['message' => 'User deleted'], Response::HTTP_OK);
    }

    /**
     * Валідує вхідні дані користувача
     * @param array $data
     * @return string|null
     */
    private function validateUserData(array $data): ?string
    {
        $login = trim($data['login'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($login)) {
            return 'Login is required!';
        }

        // Перевірка довжини
        $length = mb_strlen($login);
        if ($length < 3 || $length > 8) {
            return 'Login must be 3-8 English letters only!';
        }

        // Перевірка символів: всі англійські букви
        if (!preg_match('/^[a-zA-Z0-9]+$/', $login)) {
            return 'Login must contain only English letters and numbers!';
        }

        if (empty($phone)) {
            return 'Phone is required!';
        }

        try {
            new MobilePhone($phone);
        } catch (\InvalidArgumentException $e) {
            return 'Invalid phone number!';
        }

        if (empty($password) || strlen($password) < 8) {
            return 'Password must be at least 8 characters!';
        }

        return null;
    }
}
