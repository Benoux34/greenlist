<?php

declare(strict_types=1);

namespace App\Species\Entity;

enum Category: string
{
    case Fauna = 'fauna';
    case Flora = 'flora';
    case Fungi = 'fungi';
    case Other = 'other';

    public static function fromKingdom(string $kingdom): self
    {
        return match ($kingdom) {
            'Animalia' => self::Fauna,
            'Plantae' => self::Flora,
            'Fungi' => self::Fungi,
            default => self::Other,
        };
    }
}
