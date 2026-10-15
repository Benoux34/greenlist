<?php

declare(strict_types=1);

namespace App\Place\Controller;

use App\Place\Dto\PlacePoint;
use App\Place\Geocoder\GeocoderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

final class NamePlaceController extends AbstractController
{
    #[Route('/places/name', name: 'places_name', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)]
        PlacePoint $point,
        GeocoderInterface $geocoder,
    ): JsonResponse {
        return $this->json(['label' => $geocoder->placeName($point->latitude, $point->longitude)]);
    }
}
