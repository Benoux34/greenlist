<?php

declare(strict_types=1);

namespace App\Species\Controller;

use App\Species\Dto\SpeciesSearch;
use App\Species\Dto\SpeciesSuggestion;
use App\Species\Inaturalist\InaturalistClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

final class AutocompleteSpeciesController extends AbstractController
{
    #[Route('/species/autocomplete', name: 'species_autocomplete', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)]
        SpeciesSearch $search,
        InaturalistClientInterface $inaturalist,
    ): JsonResponse {
        $taxa = $inaturalist->autocomplete(trim($search->q), $search->category);

        return $this->json(array_map(SpeciesSuggestion::fromTaxon(...), $taxa));
    }
}
