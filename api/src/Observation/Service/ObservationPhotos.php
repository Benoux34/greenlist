<?php

declare(strict_types=1);

namespace App\Observation\Service;

use App\Observation\Entity\Observation;
use App\Observation\Exception\PhotoNotFoundException;
use App\Observation\Repository\ObservationRepository;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class ObservationPhotos
{
    public function __construct(
        private FilesystemOperator $photosStorage,
        private ObservationRepository $observations,
    ) {
    }

    public function add(Observation $observation, UploadedFile $file): void
    {
        $name = bin2hex(random_bytes(16)).'.'.$file->guessExtension();
        $observation->addPhoto($name);

        $this->photosStorage->write($this->path($observation, $name), $file->getContent());
        $this->observations->save($observation);
    }

    /**
     * @return resource
     */
    public function read(Observation $observation, string $name)
    {
        if (!\in_array($name, $observation->getPhotos(), true)) {
            throw new PhotoNotFoundException('This observation has no such photo.');
        }

        return $this->photosStorage->readStream($this->path($observation, $name));
    }

    public function remove(Observation $observation, string $name): void
    {
        $observation->removePhoto($name);

        $this->observations->save($observation);
        $this->photosStorage->delete($this->path($observation, $name));
    }

    public function removeAll(Observation $observation): void
    {
        $this->photosStorage->deleteDirectory($this->folder($observation));
    }

    private function folder(Observation $observation): string
    {
        return 'observations/'.$observation->getId();
    }

    private function path(Observation $observation, string $name): string
    {
        return $this->folder($observation).'/'.$name;
    }
}
