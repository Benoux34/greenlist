type SpeciesCategory = "fauna" | "flora" | "fungi" | "other";

type SpeciesSummary = Readonly<{
  inatId: number;
  scientificName: string;
  vernacularName: string | null;
  category: SpeciesCategory;
}>;

type SpeciesSuggestion = SpeciesSummary & Readonly<{ photoUrl: string | null }>;

type SpeciesDetail = SpeciesSummary &
  Readonly<{
    kingdom: string;
    phylum: string | null;
    taxonClass: string | null;
    taxonOrder: string | null;
    family: string | null;
    description: string | null;
    wikipediaUrl: string | null;
  }>;

export type {
  SpeciesCategory,
  SpeciesDetail,
  SpeciesSuggestion,
  SpeciesSummary,
};
