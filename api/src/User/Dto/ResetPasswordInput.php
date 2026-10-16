<?php

declare(strict_types=1);

namespace App\User\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ResetPasswordInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,

        #[Assert\NotBlank]
        #[Assert\Regex('/^[A-Za-z0-9]{6}$/')]
        public string $code,

        #[Assert\NotBlank]
        #[Assert\Length(min: 8, max: 4096)]
        public string $password,
    ) {
    }
}
