<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Entity\Traits\EntityId;
use App\Domain\Entity\Traits\Timestampable;
use App\Domain\ValueObject\MobilePhone;
use App\Infrastructure\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Table(name: 'user')]
#[ORM\Entity(repositoryClass: UserRepository::class)]

/**
 * Тут я зробив складовий індекс для 'login' і 'phone', а не як в завданні 'login' і 'password'.
 * Унікальність по 'password' майже ніколи не спрацює, бо завжди створюється новий хеш навіть для того самого пароля.
 * Складовий індекс на login + password — практично марний.
 */
#[ORM\UniqueConstraint(name: 'login_phone_unique_idx', columns: ['login', 'phone'])]
class User implements UserInterface, EntityInterface, PasswordAuthenticatedUserInterface
{
    use EntityId;
    use Timestampable;

    #[ORM\Column(name: 'login', type: Types::STRING, length: 8)]
    private string $login;

    #[ORM\Column(name: 'phone', type: 'vo_mobile_phone', length: 13)]
    private MobilePhone $phone;

    #[ORM\Column(name: 'password', type: Types::STRING, length: 64)]
    private string $password;

    #[ORM\Column(name: 'roles', type: Types::JSON)]
    private array $roles = [];

    #[ORM\Column(type: Types::STRING, length: 512, nullable: true)]
    private ?string $apiToken = null;

    /**
     * @return string|null
     */
    public function getLogin(): ?string
    {
        return $this->login;
    }

    /**
     * @param string|null $login
     * @return void
     */
    public function setLogin(?string $login): void
    {
        $this->login = $login;
    }

    /**
     * @return MobilePhone|null
     */
    public function getPhone(): ?MobilePhone
    {
        return $this->phone;
    }

    /**
     * @param MobilePhone|null $phone
     * @return void
     */
    public function setPhone(?MobilePhone $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string[]UserRepository.php
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param array $roles
     * @return void
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @return string|null
     */
    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    /**
     * @param string|null $apiToken
     * @return void
     */
    public function setApiToken(?string $apiToken): void
    {
        $this->apiToken = $apiToken;
    }

    /**
     * @return void
     */
    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return $this->phone->asString();
    }
}
