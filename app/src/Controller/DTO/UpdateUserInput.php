<?php

declare(strict_types=1);

namespace App\Controller\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class UpdateUserInput
{
    #[Assert\NotBlank(message: 'ID cannot be empty')]
    #[Assert\Type(type: 'integer', message: 'ID must be an integer')]
    #[Assert\Positive(message: 'ID must be a positive number')]
    public int $id;

    #[Assert\NotBlank(message: 'Login cannot be empty')]
    #[Assert\Length(
        min: 3,
        max: 8,
        minMessage: 'Login must be at least {{ limit }} characters long',
        maxMessage: 'Login cannot be longer than {{ limit }} characters'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9]+$/',
        message: 'Login must contain only English letters and numbers'
    )]
    public string $login;

    #[Assert\NotBlank(message: 'Password cannot be empty')]
    #[Assert\Length(
        min: 3,
        max: 8,
        minMessage: 'Password must be at least {{ limit }} characters long',
        maxMessage: 'Password cannot be longer than {{ limit }} characters'
    )]
    public string $password;

    #[Assert\NotBlank(message: 'Phone number cannot be empty')]
    #[Assert\Regex(
        pattern: '/^\+?[0-9]{10,15}$/',
        message: 'Phone number must be valid and contain 10-15 digits'
    )]
    public string $phone;
}
