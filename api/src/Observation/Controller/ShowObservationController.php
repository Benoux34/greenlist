<?php

declare(strict_types=1);

namespace App\Observation\Controller;

use App\Observation\Entity\Observation;
use App\Observation\Security\ObservationVoter;
use App\Observation\Service\DescribeObservation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ShowObservationController extends AbstractController
{
    #[Route('/observations/{id}', name: 'observations_show', requirements: ['id' => Requirement::UUID], methods: ['GET'])]
    #[IsGranted(ObservationVoter::VIEW, 'observation')]
    public function __invoke(Observation $observation, DescribeObservation $describe): JsonResponse
    {
        return $this->json($describe($observation));
    }
}
