<?php

declare(strict_types=1);

namespace App\User\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ResetCodeInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,

        #[Assert\NotBlank]
        #[Assert\Regex('/^[A-Za-z0-9]{6}$/')]
        public string $code,
    ) {
    }
}
