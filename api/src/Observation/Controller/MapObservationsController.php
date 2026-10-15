<?php

declare(strict_types=1);

namespace App\Observation\Controller;

use App\Observation\Dto\ObservationOutput;
use App\Observation\Repository\ObservationRepository;
use App\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class MapObservationsController extends AbstractController
{
    #[Route('/observations/map', name: 'observations_map', methods: ['GET'])]
    public function __invoke(#[CurrentUser] User $user, ObservationRepository $observations): JsonResponse
    {
        return $this->json(array_map(ObservationOutput::fromEntity(...), $observations->findAllByAuthor($user)));
    }
}
