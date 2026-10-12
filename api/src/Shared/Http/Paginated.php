<?php

declare(strict_types=1);

namespace App\Shared\Http;

/**
 * @template T
 */
final readonly class Paginated
{
    /**
     * @param list<T> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $perPage,
    ) {
    }
}
