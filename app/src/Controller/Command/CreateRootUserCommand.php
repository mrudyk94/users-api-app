<?php

declare(strict_types=1);

namespace App\Controller\Command;

use App\Domain\Entity\User;
use App\Domain\ValueObject\MobilePhone;
use App\Domain\ValueObject\Password;
use App\Infrastructure\Repository\UserRepository;
use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:user:create-root',
    description: 'Create root user with JWT token for 1 year'
)]
class CreateRootUserCommand extends Command
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
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addArgument('phone', InputArgument::REQUIRED)
            ->addArgument('password', InputArgument::REQUIRED);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Параметри які отримуємо з консольної команди
        $phone = $input->getArgument('phone');
        $password = new Password($input->getArgument('password'));

        // Створюємо користувача
        $user = new User();
        $user->setLogin('root');
        $user->setPassword($this->passwordHasher->hashPassword($user, $password->asString()));
        $user->setPhone(new MobilePhone($phone));
        $user->setRoles(['ROLE_ROOT']);

        // Генеруємо JWT на рік
        $now = new DateTimeImmutable();
        $expires = $now->modify('+1 year');

        $jwt = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($this->jwtKey)
        );

        $token = $jwt->builder()
            ->issuedAt($now)
            ->expiresAt($expires)
            ->withClaim('phone', $user->getPhone()->asString())
            ->withClaim('roles', $user->getRoles())
            ->getToken($jwt->signer(), $jwt->signingKey());

        $user->setApiToken($token->toString());

        // Додаємо користувача в базу даних
        $this->userRepository->saveAndFlush($user);

        $io->success(sprintf(
            "✔ Root user created. JWT Token (1 year): %s",
            $token->toString()
        ));

        $io->table(
            ['Field', 'Value'],
            [
                ['id', $user->getId()],
                ['login', $user->getLogin()],
                ['phone', $user->getPhone()],
                ['password', $password],
                ['token', $user->getApiToken()],
            ]
        );

        return Command::SUCCESS;
    }
}
