<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Application\Port\Repository\UserRepositoryInterface;
use App\Domain\Entity\User;
use App\Domain\ValueObject\MobilePhone;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends DoctrineEntityRepository implements UserRepositoryInterface
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * {@inheritDoc}
     */
    public function findById(int $id): ?User
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * {@inheritDoc}
     */
    public function findByLoginAndPhone(string $login, MobilePhone $phone): ?User
    {
        $qb = $this->createQueryBuilder('u');
        $qb->addSelect('u')
            ->where('u.login = :login AND u.phone = :phone')
            ->setParameter('login', $login)
            ->setParameter('phone', $phone);
        $query = $qb->getQuery();
        return $query->getOneOrNullResult();
    }

    /**
     * {@inheritDoc}
     */
    public function findByApiToken(string $token): ?User
    {
        $qb = $this->createQueryBuilder('u');
        $qb->addSelect('u')
            ->where('u.apiToken = :token')
            ->setParameter('token', $token);
        $query = $qb->getQuery();
        return $query->getOneOrNullResult();
    }
}
