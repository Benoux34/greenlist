<?php

declare(strict_types=1);

namespace App\Tests\Unit\Observation;

use App\Observation\Entity\Observation;
use App\Observation\Entity\Visibility;
use App\Observation\Exception\PhotoNotFoundException;
use App\Observation\Exception\TooManyPhotosException;
use App\Species\Entity\Species;
use App\User\Entity\User;
use PHPUnit\Framework\TestCase;

final class ObservationTest extends TestCase
{
    public function testObservedAtIsStoredInUtc(): void
    {
        $observation = $this->observation(observedAt: new \DateTimeImmutable('2026-06-01 10:30:00', new \DateTimeZone('Europe/Paris')));

        self::assertSame('UTC', $observation->getObservedAt()->getTimezone()->getName());
        self::assertSame('2026-06-01 08:30:00', $observation->getObservedAt()->format('Y-m-d H:i:s'));
    }

    public function testNotesAreTrimmedAndBlankNotesBecomeNull(): void
    {
        self::assertSame('Près du ruisseau', $this->observation(notes: "  Près du ruisseau \n")->getNotes());
        self::assertNull($this->observation(notes: '   ')->getNotes());
        self::assertNull($this->observation(notes: null)->getNotes());
    }

    public function testEditReplacesEveryField(): void
    {
        $observation = $this->observation();
        $oak = new Species(56133, 'Quercus robur', 'Chêne pédonculé', 'Plantae');

        $observation->edit($oak, new \DateTimeImmutable('2026-05-02T07:00:00+02:00'), 45.5, 5.5, 3, ' Un grand chêne ', Visibility::Private);

        self::assertSame($oak, $observation->getSpecies());
        self::assertSame('2026-05-02T05:00:00+00:00', $observation->getObservedAt()->format(\DATE_ATOM));
        self::assertSame(45.5, $observation->getLatitude());
        self::assertSame(5.5, $observation->getLongitude());
        self::assertSame(3, $observation->getCount());
        self::assertSame('Un grand chêne', $observation->getNotes());
        self::assertSame(Visibility::Private, $observation->getVisibility());
    }

    public function testFriendsSeeANewObservationUnlessToldOtherwise(): void
    {
        self::assertSame(Visibility::Friends, $this->observation()->getVisibility());
    }

    public function testAnObservationHoldsThreePhotosAtMost(): void
    {
        $observation = $this->observation();
        foreach (['a.jpg', 'b.jpg', 'c.png'] as $name) {
            $observation->addPhoto($name);
        }

        $this->expectException(TooManyPhotosException::class);
        $observation->addPhoto('d.jpg');
    }

    public function testRemovingAPhotoKeepsTheOthersInOrder(): void
    {
        $observation = $this->observation();
        foreach (['a.jpg', 'b.jpg', 'c.png'] as $name) {
            $observation->addPhoto($name);
        }

        $observation->removePhoto('b.jpg');

        self::assertSame(['a.jpg', 'c.png'], $observation->getPhotos());
    }

    public function testRemovingAnUnknownPhotoFails(): void
    {
        $this->expectException(PhotoNotFoundException::class);
        $this->observation()->removePhoto('missing.jpg');
    }

    private function observation(
        ?\DateTimeImmutable $observedAt = null,
        ?string $notes = null,
    ): Observation {
        return new Observation(
            new User('ana@example.fr', 'Ana', 'hash'),
            new Species(42069, 'Vulpes vulpes', 'Renard roux', 'Animalia'),
            $observedAt ?? new \DateTimeImmutable('2026-06-01T08:00:00Z'),
            46.2,
            6.1,
            2,
            $notes,
        );
    }
}
