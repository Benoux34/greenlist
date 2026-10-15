<?php

declare(strict_types=1);

namespace App\Observation\Controller;

use App\Observation\Entity\Observation;
use App\Observation\Security\ObservationVoter;
use App\Observation\Service\DeleteObservation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DeleteObservationController extends AbstractController
{
    #[Route('/observations/{id}', name: 'observations_delete', requirements: ['id' => Requirement::UUID], methods: ['DELETE'])]
    #[IsGranted(ObservationVoter::EDIT, 'observation')]
    public function __invoke(Observation $observation, DeleteObservation $deleteObservation): Response
    {
        $deleteObservation($observation);

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
