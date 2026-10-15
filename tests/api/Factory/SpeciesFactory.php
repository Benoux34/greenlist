<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Species\Entity\Species;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Species>
 */
final class SpeciesFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Species::class;
    }

    public static function fox(): Species
    {
        return self::findOrCreate([
            'inatId' => 42069,
            'scientificName' => 'Vulpes vulpes',
            'vernacularName' => 'Renard roux',
            'kingdom' => 'Animalia',
            'phylum' => 'Chordata',
            'taxonClass' => 'Mammalia',
            'taxonOrder' => 'Carnivora',
            'family' => 'Canidae',
        ]);
    }

    protected function defaults(): array
    {
        return [
            'inatId' => self::faker()->unique()->numberBetween(100_000, 999_999),
            'scientificName' => ucfirst(self::faker()->words(2, true)),
            'vernacularName' => self::faker()->word(),
            'kingdom' => self::faker()->randomElement(['Animalia', 'Plantae', 'Fungi']),
        ];
    }
}
