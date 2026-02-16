<?php

namespace App\Application\Port\Repository;

use App\Application\Exception\AppException;
use App\Domain\Entity\EntityInterface;

interface EntityRepositoryInterface
{
    /**
     * @param EntityInterface $entity
     * @throws AppException
     */
    public function saveAndFlush(EntityInterface $entity): mixed;

    /**
     * @param EntityInterface $entity
     * @throws AppException
     */
    public function deleteAndFlash(EntityInterface $entity): void;
}
