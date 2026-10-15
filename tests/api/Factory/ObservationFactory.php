<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Observation\Entity\Observation;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Observation>
 */
final class ObservationFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Observation::class;
    }

    protected function defaults(): array
    {
        return [
            'author' => UserFactory::new(),
            'species' => SpeciesFactory::new(),
            'observedAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTimeBetween('-1 year')),
            'latitude' => self::faker()->latitude(),
            'longitude' => self::faker()->longitude(),
        ];
    }
}
