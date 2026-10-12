<?php

declare(strict_types=1);

namespace App\Place\Geocoder;

use App\Place\Dto\PlaceSuggestion;

interface GeocoderInterface
{
    /**
     * Addresses and places matching the text, best match first.
     *
     * @return list<PlaceSuggestion>
     */
    public function search(string $text): array;
}
