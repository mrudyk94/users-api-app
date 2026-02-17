<?php

declare(strict_types=1);

namespace App\Controller\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class LoginInput
{
    public function __construct(
        #[Assert\NotBlank(message: 'Login is required')]
        #[Assert\Type('string')]
        public string $login,

        #[Assert\NotBlank(message: 'Password is required')]
        #[Assert\Type('string')]
        public string $password
    ) {
    }
}
