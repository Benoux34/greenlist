<?php

declare(strict_types=1);

namespace App\Observation\Controller;

use App\Observation\Entity\Observation;
use App\Observation\Security\ObservationVoter;
use App\Observation\Service\ObservationPhotos;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ShowObservationPhotoController extends AbstractController
{
    #[Route(
        '/observations/{id}/photos/{name}',
        name: 'observations_photos_show',
        requirements: ['id' => Requirement::UUID, 'name' => '[a-f0-9]{32}\.(jpg|png)'],
        methods: ['GET'],
    )]
    #[IsGranted(ObservationVoter::VIEW, 'observation')]
    public function __invoke(Observation $observation, string $name, ObservationPhotos $photos): StreamedResponse
    {
        $stream = $photos->read($observation, $name);

        return new StreamedResponse(
            static function () use ($stream): void {
                fpassthru($stream);
            },
            headers: [
                'Content-Type' => str_ends_with($name, '.png') ? 'image/png' : 'image/jpeg',
                'Cache-Control' => 'private, max-age=31536000, immutable',
            ],
        );
    }
}
