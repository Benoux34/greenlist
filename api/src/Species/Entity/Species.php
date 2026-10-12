<?php

declare(strict_types=1);

namespace App\Species\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use App\Species\Repository\SpeciesRepository;

#[ORM\Entity(repositoryClass: SpeciesRepository::class)]
#[ORM\Index(name: 'species_category_idx', fields: ['category'])]
final class Species
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $id;

    #[ORM\Column(unique: true)]
    private int $inatId;

    #[ORM\Column(length: 255)]
    private string $scientificName;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $vernacularName;

    #[ORM\Column(length: 100)]
    private string $kingdom;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $phylum;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $taxonClass;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $taxonOrder;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $family;

    #[ORM\Column(enumType: Category::class)]
    private Category $category;

    public function __construct(
        int $inatId,
        string $scientificName,
        ?string $vernacularName,
        string $kingdom,
        ?string $phylum = null,
        ?string $taxonClass = null,
        ?string $taxonOrder = null,
        ?string $family = null,
    ) {
        $this->id = Uuid::v7();
        $this->inatId = $inatId;
        $this->scientificName = $scientificName;
        $this->vernacularName = $vernacularName;
        $this->kingdom = $kingdom;
        $this->phylum = $phylum;
        $this->taxonClass = $taxonClass;
        $this->taxonOrder = $taxonOrder;
        $this->family = $family;
        $this->category = Category::fromKingdom($kingdom);
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getScientificName(): string
    {
        return $this->scientificName;
    }

    public function getVernacularName(): ?string
    {
        return $this->vernacularName;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }
}
