<?php

declare(strict_types=1);

namespace App\Observation\Controller;

use App\Observation\Dto\ObservationFilter;
use App\Observation\Dto\ObservationOutput;
use App\Observation\Repository\ObservationRepository;
use App\Shared\Http\Paginated;
use App\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class ListObservationsController extends AbstractController
{
    #[Route('/observations', name: 'observations_list', methods: ['GET'])]
    public function __invoke(
        #[CurrentUser] User $user,
        ObservationRepository $observations,
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)]
        ObservationFilter $filter = new ObservationFilter(),
    ): JsonResponse {
        $page = $observations->findPage($user, $filter);

        return $this->json(new Paginated(
            array_map(ObservationOutput::fromEntity(...), $page->items),
            $page->total,
            $page->page,
            $page->perPage,
        ));
    }
}
