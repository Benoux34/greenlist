<?php

declare(strict_types=1);

namespace App\Observation\Controller;

use App\Observation\Dto\ObservationOutput;
use App\Observation\Entity\Observation;
use App\Observation\Security\ObservationVoter;
use App\Observation\Service\ObservationPhotos;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class RemoveObservationPhotoController extends AbstractController
{
    #[Route(
        '/observations/{id}/photos/{name}',
        name: 'observations_photos_remove',
        requirements: ['id' => Requirement::UUID, 'name' => '[a-f0-9]{32}\.(jpg|png)'],
        methods: ['DELETE'],
    )]
    #[IsGranted(ObservationVoter::EDIT, 'observation')]
    public function __invoke(Observation $observation, string $name, ObservationPhotos $photos): JsonResponse
    {
        $photos->remove($observation, $name);

        return $this->json(ObservationOutput::fromEntity($observation));
    }
}
