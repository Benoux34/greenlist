<?php

declare(strict_types=1);

namespace App\Observation\Controller;

use App\Observation\Dto\ObservationInput;
use App\Observation\Dto\ObservationOutput;
use App\Observation\Entity\Observation;
use App\Observation\Security\ObservationVoter;
use App\Observation\Service\UpdateObservation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class UpdateObservationController extends AbstractController
{
    #[Route('/observations/{id}', name: 'observations_update', requirements: ['id' => Requirement::UUID], methods: ['PATCH'])]
    #[IsGranted(ObservationVoter::EDIT, 'observation')]
    public function __invoke(
        Observation $observation,
        #[MapRequestPayload] ObservationInput $input,
        UpdateObservation $updateObservation,
    ): JsonResponse {
        $updateObservation($observation, $input);

        return $this->json(ObservationOutput::fromEntity($observation));
    }
}
