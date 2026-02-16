<?php

namespace App\Domain\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait EntityId
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }
}
