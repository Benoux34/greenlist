<?php

declare(strict_types=1);

namespace App\User\Redlist;

use Symfony\Component\Uid\Uuid;

final readonly class RedlistIdentity
{
    public function __construct(
        public Uuid $id,
        public string $email,
        public string $pseudo,
    ) {
    }
}