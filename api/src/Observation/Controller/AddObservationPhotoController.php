<?php

declare(strict_types=1);

namespace App\Observation\Controller;

use App\Observation\Dto\ObservationOutput;
use App\Observation\Entity\Observation;
use App\Observation\Security\ObservationVoter;
use App\Observation\Service\ObservationPhotos;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints as Assert;

final class AddObservationPhotoController extends AbstractController
{
    #[Route('/observations/{id}/photos', name: 'observations_photos_add', requirements: ['id' => Requirement::UUID], methods: ['POST'])]
    #[IsGranted(ObservationVoter::EDIT, 'observation')]
    public function __invoke(
        Observation $observation,
        #[MapUploadedFile(new Assert\File(maxSize: '1M', mimeTypes: ['image/jpeg', 'image/png']))]
        UploadedFile $photo,
        ObservationPhotos $photos,
    ): JsonResponse {
        $photos->add($observation, $photo);

        return $this->json(ObservationOutput::fromEntity($observation), Response::HTTP_CREATED);
    }
}
