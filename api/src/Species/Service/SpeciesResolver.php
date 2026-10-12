<?php

declare(strict_types=1);

namespace App\Species\Service;

use App\Species\Entity\Species;
use App\Species\Exception\SpeciesNotFoundException;
use App\Species\Inaturalist\InaturalistClientInterface;
use App\Species\Repository\SpeciesRepository;

final readonly class SpeciesResolver
{
    public function __construct(
        private SpeciesRepository $species,
        private InaturalistClientInterface $inaturalist,
    ) {
    }

    public function resolve(int $inatId): Species
    {
        $species = $this->species->findOneBy(['inatId' => $inatId]);
        if (null !== $species) {
            return $species;
        }

        $taxon = $this->inaturalist->find($inatId);
        if (null === $taxon || 'species' !== $taxon->rank) {
            throw new SpeciesNotFoundException(\sprintf('No species with the iNaturalist id %d.', $inatId));
        }

        $species = new Species(
            $taxon->inatId,
            $taxon->scientificName,
            $taxon->vernacularName,
            $taxon->kingdom,
            $taxon->phylum,
            $taxon->taxonClass,
            $taxon->taxonOrder,
            $taxon->family,
        );
        $this->species->add($species);

        return $species;
    }
}
