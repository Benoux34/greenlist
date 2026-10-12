<?php

declare(strict_types=1);

namespace App\Place\Controller;

use App\Place\Dto\PlaceSearch;
use App\Place\Geocoder\GeocoderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

final class SearchPlacesController extends AbstractController
{
    #[Route('/places/search', name: 'places_search', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)]
        PlaceSearch $search,
        GeocoderInterface $geocoder,
    ): JsonResponse {
        return $this->json($geocoder->search(trim($search->q)));
    }
}
