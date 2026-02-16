<?php

declare(strict_types=1);

namespace App\Application\Port\Repository;

use App\Domain\Entity\User;
use App\Domain\ValueObject\MobilePhone;

interface UserRepositoryInterface extends EntityRepositoryInterface
{
    /**
     * Отримати користувача по ID
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User;

    /**
     * Отримати користувача по номеру телефона
     * @param string $login
     * @param MobilePhone $phone
     * @return User|null
     */
    public function findByLoginAndPhone(string $login, MobilePhone $phone): ?User;

    /**
     * Отримати користувача по token
     * @param string $token
     * @return User|null
     */
    public function findByApiToken(string $token): ?User;
}
