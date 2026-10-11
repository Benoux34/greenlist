<?php

declare(strict_types=1);

namespace App\User\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class RegisterUserInput
{
    public function __construct(
        #[Assert\NotBlank(normalizer: 'trim')]
        #[Assert\Length(max: 100)]
        public string $firstName,

        #[Assert\NotBlank]
        #[Assert\Email]
        #[Assert\Length(max: 180)]
        public string $email,

        #[Assert\NotBlank]
        #[Assert\Length(min: 8, max: 4096)]
        public string $password,
    ) {
    }
}