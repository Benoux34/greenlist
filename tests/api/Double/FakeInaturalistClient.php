<?php

declare(strict_types=1);

namespace App\Tests\Double;

use App\Species\Entity\Category;
use App\Species\Inaturalist\InaturalistClientInterface;
use App\Species\Inaturalist\Taxon;

final class FakeInaturalistClient implements InaturalistClientInterface
{
    public function autocomplete(string $query, ?Category $category = null): array
    {
        $query = self::fold($query);

        return array_values(array_filter(
            self::taxa(),
            static fn (Taxon $taxon): bool => 'species' === $taxon->rank
                && (null === $category || Category::fromKingdom($taxon->kingdom) === $category)
                && (str_contains(self::fold($taxon->vernacularName ?? ''), $query)
                    || str_contains(self::fold($taxon->scientificName), $query)),
        ));
    }

    public function find(int $inatId): ?Taxon
    {
        foreach (self::taxa() as $taxon) {
            if ($taxon->inatId === $inatId) {
                return $taxon;
            }
        }

        return null;
    }

    private static function fold(string $text): string
    {
        return mb_strtolower((string) preg_replace('/\p{Mn}/u', '', (string) \Normalizer::normalize($text, \Normalizer::FORM_D)));
    }

    /**
     * @return list<Taxon>
     */
    private static function taxa(): array
    {
        return [
            new Taxon(42069, 'Vulpes vulpes', 'Renard roux', 'species', 'Animalia', 'Chordata', 'Mammalia', 'Carnivora', 'Canidae'),
            new Taxon(56133, 'Quercus robur', 'Chêne pédonculé', 'species', 'Plantae', 'Tracheophyta', 'Magnoliopsida', 'Fagales', 'Fagaceae'),
            new Taxon(48715, 'Amanita muscaria', 'Amanite tue-mouches', 'species', 'Fungi', 'Basidiomycota', 'Agaricomycetes', 'Agaricales', 'Amanitaceae'),
            new Taxon(47126, 'Plantae', 'Plantes', 'kingdom', 'Plantae'),
        ];
    }
}
