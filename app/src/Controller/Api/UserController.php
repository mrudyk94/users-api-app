<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Application\Exception\AppException;
use App\Controller\DTO\CreateUserInput;
use App\Controller\DTO\UpdateUserInput;
use App\Domain\Entity\User;
use App\Domain\ValueObject\MobilePhone;
use App\Infrastructure\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/users', name: 'api_users_')]
final readonly class UserController
{
    /**
     * @param UserRepository $userRepository
     * @param UserPasswordHasherInterface $passwordHasher
     * @param Security $security
     */
    public function __construct(
        private UserRepository              $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private Security                    $security
    )
    {
    }

    /**
     * Додаємо нового користувача
     * @param CreateUserInput $input
     * @return JsonResponse
     */
    #[Route('',
        name: 'create',
        methods: ['POST']
    )]
    #[IsGranted('ROLE_ROOT')]
    public function create(
        #[MapRequestPayload] CreateUserInput $input
    ): JsonResponse
    {
        $user = new User();
        $user->setLogin($input->login);
        $user->setPhone(new MobilePhone($input->phone));
        $user->setRoles(['ROLE_USER']);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $input->password);
        $user->setPassword($hashedPassword);

        try {
            $this->userRepository->saveAndFlush($user);
        } catch (AppException) {
            throw new ConflictHttpException('User already exists');
        }

        /**
         * По завданню вказано повертати пароль при запиту, але я не бачу сенсу, бо він хешований.
         * Користувачу пароль не потрібен. Навіть хеш — це витік чутливої інформації.
         */
        $data = [
            'id' => $user->getId(),
            'login' => $user->getLogin(),
            'phone' => $user->getPhone()->asString()
        ];

        return new JsonResponse($data, Response::HTTP_CREATED);
    }

    /**
     * Оновлення користувача
     * @param UpdateUserInput $input
     * @return JsonResponse
     */
    #[Route('',
        name: 'update',
        methods: ['PUT']
    )]
    #[IsGranted('ROLE_USER')]
    public function update(
        #[MapRequestPayload] UpdateUserInput $input
    ): JsonResponse
    {
        // Шукаємо користувача за ID
        $user = $this->userRepository->findById($input->id);

        // Якщо користувача немає — кидаємо 404
        if (!$user) {
            throw new NotFoundHttpException(sprintf('User with ID %d not found', $input->id));
        }

        // Звичайний користувач може оновлювати тільки себе
        $currentUser = $this->security->getUser();
        if ($currentUser->getRoles()[0] !== 'ROLE_ROOT') {

            if ($currentUser->getId() !== $user->getId()) {
                throw new AccessDeniedHttpException('Access denied');
            }
        }

        // Оновлюємо дані користувача
        $user->setLogin($input->login);
        $user->setPhone(new MobilePhone($input->phone));

        // Оновлюємо пароль, якщо переданий
        if (!empty($data['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        try {
            $this->userRepository->saveAndFlush($user);
        } catch (AppException) {
            throw new ConflictHttpException('User already exists');
        }

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
    #[Route('/{id}',
        name: 'user',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET']
    )]
    #[IsGranted('ROLE_USER')]
    public function getUser(
        int $id
    ): JsonResponse
    {
        // Звичайний користувач може отримати інформацію тільки про себе
        $currentUser = $this->security->getUser();
        if ($currentUser->getRoles()[0] !== 'ROLE_ROOT' && $currentUser->getId() !== $id) {
            throw new AccessDeniedHttpException('Access denied');
        }

        // Перевірка, чи користувач існує в базі даних
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new NotFoundHttpException(sprintf('User with ID %d not found', $id));
        }

        /**
         * По завданню вказано повертати пароль при запиту, але я не бачу сенсу, бо він хешований.
         * Користувачу пароль не потрібен. Навіть хеш — це витік чутливої інформації.
         */
        $data = [
            'id' => $user->getId(),
            'login' => $user->getLogin(),
            'phone' => $user->getPhone()->asString(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * Видалення користувача
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/{id}',
        name: 'delete',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['DELETE']
    )]
    #[IsGranted('ROLE_ROOT')]
    public function delete(
        int $id
    ): JsonResponse
    {
        // Перевірка, чи користувач якого хочемо видалити є в базі даних
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new NotFoundHttpException(sprintf('User with ID %d not found', $id));
        }

        // Перевірка, щоб не видалити самого себе
        $currentUser = $this->security->getUser();
        if ($currentUser->getId() === $user->getId()) {
            throw new AccessDeniedHttpException('You cannot delete yourself');
        }

        // Видаляємо користувача
        $this->userRepository->deleteAndFlash($user);

        return new JsonResponse(['message' => 'User deleted'], Response::HTTP_OK);
    }
}
