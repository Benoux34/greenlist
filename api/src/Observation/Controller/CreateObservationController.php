<?php

declare(strict_types=1);

namespace App\Observation\Controller;

use App\Observation\Dto\ObservationInput;
use App\Observation\Dto\ObservationOutput;
use App\Observation\Service\CreateObservation;
use App\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class CreateObservationController extends AbstractController
{
    #[Route('/observations', name: 'observations_create', methods: ['POST'])]
    public function __invoke(
        #[CurrentUser] User $user,
        #[MapRequestPayload] ObservationInput $input,
        CreateObservation $createObservation,
    ): JsonResponse {
        $observation = $createObservation($user, $input);

        return $this->json(ObservationOutput::fromEntity($observation), Response::HTTP_CREATED);
    }
}
