<?php

declare(strict_types=1);

namespace App\Place\Geocoder;

interface GeocoderInterface
{
    /**
     * The town or place closest to a point, or null when it cannot be told.
     */
    public function placeName(float $latitude, float $longitude): ?string;
}
