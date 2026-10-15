<?php

declare(strict_types=1);

namespace App\Tests\Double;

use App\Place\Geocoder\GeocoderInterface;

final class FakeGeocoder implements GeocoderInterface
{
    public const string PLACE = 'Lyon, Rhône, France';

    public function placeName(float $latitude, float $longitude): ?string
    {
        return self::PLACE;
    }
}
